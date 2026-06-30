from fastapi import FastAPI, BackgroundTasks
from pydantic import BaseModel
from typing import List, Optional
import joblib
import os
import numpy as np
import pandas as pd
import scipy.sparse as sp
from sklearn.ensemble import RandomForestClassifier
from sklearn.feature_extraction.text import TfidfVectorizer

app = FastAPI(title="Student Risk Assessment API", version="2.0")

base_dir = os.path.dirname(__file__)
model_path = os.path.join(base_dir, 'risk_model.pkl')
vectorizer_path = os.path.join(base_dir, 'vectorizer.pkl')

model = None
vectorizer = None

def load_models():
    global model, vectorizer
    try:
        model = joblib.load(model_path)
        vectorizer = joblib.load(vectorizer_path)
        print("Model and Vectorizer loaded successfully.")
    except Exception as e:
        print(f"Warning: Could not load models. Error: {e}")
        model = None
        vectorizer = None

# Load on startup
load_models()

# Input Schema for Prediction
class StudentProfile(BaseModel):
    tardiness: int
    misconduct: int
    total_absences: int
    behavioral_reports_count: int
    failed_subjects: int
    referral_reason: str = ""

# Input Schema for Retraining
class TrainingRecord(BaseModel):
    tardiness: int
    misconduct: int
    total_absences: int
    behavioral_reports_count: int
    failed_subjects: int
    referral_reason: str
    risk_level: str # 'low', 'moderate', 'high'

class RetrainPayload(BaseModel):
    records: List[TrainingRecord]

risk_decoder = {0: 'low', 1: 'moderate', 2: 'high'}
risk_encoder = {'low': 0, 'moderate': 1, 'high': 2}

@app.get("/")
def read_root():
    return {"status": "online", "message": "ML Risk Assessment API is running"}

@app.post("/predict")
def predict_risk(student: StudentProfile):
    if model is None or vectorizer is None:
        return {"error": "Models not loaded. Please train the model first."}
    
    # 1. Prepare numerical features
    numeric_features = np.array([[
        student.tardiness,
        student.misconduct,
        student.total_absences,
        student.behavioral_reports_count,
        student.failed_subjects
    ]])
    
    # 2. Prepare text features (NLP)
    text_features = vectorizer.transform([student.referral_reason])
    
    # 3. Combine them
    features = sp.hstack([numeric_features, text_features])
    
    # 4. Predict Risk Level (Low, Moderate, High)
    prediction_idx = model.predict(features)[0]
    risk_level = risk_decoder[prediction_idx]
    
    # 5. Calculate a Risk Score (0-100) based on probability of the predicted class
    probabilities = model.predict_proba(features)[0]
    base_score = 0
    if risk_level == 'low':
        base_score = 10 + (probabilities[0] * 20)  # 10 - 30
    elif risk_level == 'moderate':
        base_score = 40 + (probabilities[1] * 25)  # 40 - 65
    else:
        base_score = 70 + (probabilities[2] * 25)  # 70 - 95
        
    risk_score = round(base_score, 2)
    
    # 6. Rule-Based Engine: Determine recommended seminar tag
    recommended_seminar_tag = "general"
    reason_lower = student.referral_reason.lower()
    
    if risk_level == 'high':
        if student.total_absences >= 10 or 'absence' in reason_lower:
            recommended_seminar_tag = "attendance_intervention"
        elif 'academic' in reason_lower or student.failed_subjects > 0 or 'failing' in reason_lower:
            recommended_seminar_tag = "academic_recovery"
        elif 'bully' in reason_lower or 'aggressive' in reason_lower:
            recommended_seminar_tag = "anti_bullying"
        else:
            recommended_seminar_tag = "values_formation"
            
    elif risk_level == 'moderate':
        if student.total_absences >= 5 or 'absence' in reason_lower:
            recommended_seminar_tag = "attendance_intervention"
        elif 'peer' in reason_lower or 'bully' in reason_lower:
            recommended_seminar_tag = "anti_bullying"
        elif 'study' in reason_lower or 'math' in reason_lower or 'assignment' in reason_lower:
            recommended_seminar_tag = "academic_recovery"
        else:
            recommended_seminar_tag = "values_formation"
            
    else:
        recommended_seminar_tag = "orientation"

    return {
        "risk_level": risk_level,
        "risk_score": risk_score,
        "recommended_seminar_tag": recommended_seminar_tag
    }

def perform_retraining(records: List[TrainingRecord]):
    global model, vectorizer
    print(f"Starting retraining with {len(records)} records...")
    
    df = pd.DataFrame([r.dict() for r in records])
    y = df['risk_level'].map(risk_encoder)
    
    # Train NLP
    new_vectorizer = TfidfVectorizer(max_features=50, stop_words='english')
    X_text = new_vectorizer.fit_transform(df['referral_reason'].fillna(''))
    
    X_numeric = df[['avg_grade', 'total_absences', 'behavioral_reports_count', 'failed_subjects']].values
    
    X_combined = sp.hstack([X_numeric, X_text])
    
    # Train Forest
    new_model = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=5)
    new_model.fit(X_combined, y)
    
    # Save
    joblib.dump(new_model, model_path)
    joblib.dump(new_vectorizer, vectorizer_path)
    
    print("Retraining completed and models saved.")
    
    # Reload models in memory
    load_models()

@app.post("/retrain")
def retrain_api(payload: RetrainPayload, background_tasks: BackgroundTasks):
    if len(payload.records) < 10:
        return {"error": "Need at least 10 records to retrain the model."}
    
    background_tasks.add_task(perform_retraining, payload.records)
    return {"message": "Retraining started in the background. The models will be updated automatically."}

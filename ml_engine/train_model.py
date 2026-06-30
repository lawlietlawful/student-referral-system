import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
from sklearn.feature_extraction.text import TfidfVectorizer
import scipy.sparse as sp
import joblib
import os
import random

print("Generating synthetic student data with text features...")

# Generate synthetic data for 1000 past students
np.random.seed(42)
random.seed(42)

# Features
tardiness = np.random.exponential(2, 1000).clip(0, 20).astype(int)
misconduct = np.random.poisson(0.3, 1000).clip(0, 5)
total_absences = np.random.exponential(4, 1000).clip(0, 30).astype(int)
behavioral_reports = np.random.poisson(0.5, 1000).clip(0, 10)
failed_subjects = np.random.poisson(0.3, 1000).clip(0, 5)

# Text Phrases for synthetic referral reasons
high_risk_phrases = ["constantly bullying others", "failing all subjects", "depressed and isolated", "very aggressive behavior", "has not attended class in weeks", "caught cheating multiple times"]
moderate_risk_phrases = ["sometimes disruptive", "needs help with math", "struggling with assignments", "lack of focus", "talking back to teacher", "frequently late to class"]
low_risk_phrases = ["general counseling needed", "career advice", "minor misunderstanding with peer", "asking about college", "needs a tutor for one subject"]

referral_reasons = []
risk_levels = []

for i in range(1000):
    score = 0
    
    # Tardiness factor
    if tardiness[i] >= 5: score += 3
    elif tardiness[i] >= 3: score += 1
    
    # Misconduct factor
    if misconduct[i] >= 2: score += 3
    elif misconduct[i] == 1: score += 2
    
    # Absences factor
    if total_absences[i] >= 10: score += 4
    elif total_absences[i] >= 5: score += 2
    
    # Behavior factor
    if behavioral_reports[i] >= 3: score += 3
    elif behavioral_reports[i] >= 1: score += 1
        
    # Failed subjects factor
    if failed_subjects[i] >= 2: score += 3
    elif failed_subjects[i] == 1: score += 1

    # Assign risk and a matching synthetic text reason
    if score >= 6:
        risk_levels.append('high')
        referral_reasons.append(random.choice(high_risk_phrases))
    elif score >= 3:
        risk_levels.append('moderate')
        referral_reasons.append(random.choice(moderate_risk_phrases))
    else:
        risk_levels.append('low')
        referral_reasons.append(random.choice(low_risk_phrases))

# Create DataFrame
df = pd.DataFrame({
    'tardiness': tardiness,
    'misconduct': misconduct,
    'total_absences': total_absences,
    'behavioral_reports_count': behavioral_reports,
    'failed_subjects': failed_subjects,
    'referral_reason': referral_reasons,
    'risk_level': risk_levels
})

# Map labels to integers for training
label_map = {'low': 0, 'moderate': 1, 'high': 2}
y = df['risk_level'].map(label_map)

print("Training NLP Vectorizer...")
vectorizer = TfidfVectorizer(max_features=50, stop_words='english')
X_text = vectorizer.fit_transform(df['referral_reason'])

X_numeric = df[['tardiness', 'misconduct', 'total_absences', 'behavioral_reports_count', 'failed_subjects']].values

# Combine numeric and text features
X_combined = sp.hstack([X_numeric, X_text])

# Split data
X_train, X_test, y_train, y_test = train_test_split(X_combined, y, test_size=0.2, random_state=42)

print("Training Random Forest Classifier with combined NLP features...")
rf_model = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=5)
rf_model.fit(X_train, y_train)

# Evaluate
y_pred = rf_model.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)
print(f"Model Accuracy on Test Data: {accuracy * 100:.2f}%")

# Save the models
base_dir = os.path.dirname(__file__)
model_path = os.path.join(base_dir, 'risk_model.pkl')
vectorizer_path = os.path.join(base_dir, 'vectorizer.pkl')

joblib.dump(rf_model, model_path)
joblib.dump(vectorizer, vectorizer_path)

print(f"Random Forest Model saved to: {model_path}")
print(f"NLP Vectorizer saved to: {vectorizer_path}")
print("Training complete! The AI is ready to use.")

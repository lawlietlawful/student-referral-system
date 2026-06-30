<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskAssessment;
use App\Models\Referral;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLController extends Controller
{
    /**
     * Retrain the ML Engine using historical data from the database.
     */
    public function retrain()
    {
        // 1. Fetch all completed/resolved referrals to use as training data.
        // In a real system, you'd use a mix of resolved referrals and historical student data.
        $referrals = Referral::with('student', 'riskAssessment')
            ->whereNotNull('risk_assessment_id') // only ones that were assessed
            ->get();

        if ($referrals->count() < 10) {
            return redirect()->back()->with('error', 'Not enough historical data to retrain the AI. Need at least 10 records.');
        }

        $trainingRecords = [];

        foreach ($referrals as $ref) {
            $student = $ref->student;
            $riskAssessment = $ref->riskAssessment;
            
            if (!$student || !$riskAssessment) {
                continue;
            }

            // We use the actual risk level determined by the system/counselor.
            // If the referral was resolved quickly, it might be low. If it escalated, high.
            // For capstone simplicity, we just train it to recognize the historical pattern that led to this referral's priority.
            
            $risk_level = 'low';
            if ($ref->priority === 'high') {
                $risk_level = 'high';
            } elseif ($ref->priority === 'moderate') {
                $risk_level = 'moderate';
            }

            $trainingRecords[] = [
                'tardiness' => $riskAssessment->tardiness ?? 0,
                'misconduct' => $riskAssessment->misconduct ?? 0,
                'total_absences' => $riskAssessment->total_absences ?? 0,
                'behavioral_reports_count' => $riskAssessment->behavioral_reports_count ?? 0,
                'failed_subjects' => $riskAssessment->failed_subjects ?? 0,
                'referral_reason' => $ref->reason ?? '',
                'risk_level' => $risk_level
            ];
        }

        // 2. Send the payload to Python FastAPI
        try {
            $response = Http::timeout(30)->post('http://127.0.0.1:8001/retrain', [
                'records' => $trainingRecords
            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'AI Retraining initiated successfully! The model is learning in the background.');
            } else {
                Log::error("Retrain API Error: " . $response->body());
                return redirect()->back()->with('error', 'Failed to communicate with the ML Engine.');
            }
        } catch (\Exception $e) {
            Log::error("Retrain API Connection Failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Could not connect to the ML Engine.');
        }
    }

    /**
     * Upload a CSV of historical data and send directly to Python.
     */
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        
        $trainingRecords = [];
        $header = null;
        
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }
                
                // Ensure we have correct column count (min 6 for the 6 required features)
                if (count($row) < 6) {
                    continue;
                }

                $data = array_combine($header, $row);
                
                if (!$data) continue;

                $trainingRecords[] = [
                    'tardiness' => (int) ($data['tardiness'] ?? 0),
                    'misconduct' => (int) ($data['misconduct'] ?? 0),
                    'total_absences' => (int) ($data['total_absences'] ?? 0),
                    'behavioral_reports_count' => (int) ($data['behavioral_reports_count'] ?? 0),
                    'failed_subjects' => (int) ($data['failed_subjects'] ?? 0),
                    'referral_reason' => $data['referral_reason'] ?? '',
                    'risk_level' => strtolower($data['risk_level'] ?? 'low')
                ];
            }
            fclose($handle);
        }

        if (count($trainingRecords) < 10) {
            return redirect()->back()->with('error', 'The CSV must contain at least 10 valid records.');
        }

        try {
            $response = Http::timeout(60)->post('http://127.0.0.1:8001/retrain', [
                'records' => $trainingRecords
            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'CSV Uploaded! AI Retraining initiated with ' . count($trainingRecords) . ' records.');
            } else {
                Log::error("Retrain API Error: " . $response->body());
                return redirect()->back()->with('error', 'Failed to communicate with the ML Engine.');
            }
        } catch (\Exception $e) {
            Log::error("Retrain API Connection Failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Could not connect to the ML Engine.');
        }
    }
}

<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Referral;
use App\Models\RiskAssessment;
use App\Models\Seminar;
use App\Models\StudentSeminar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RiskAssessmentService
{
    protected $apiUrl = 'http://127.0.0.1:8001/predict';

    /**
     * Process a referral: Call ML API, create Risk Assessment, and Auto-assign Seminar.
     */
    public function assessAndAssignSeminar(Student $student, Referral $referral)
    {
        // 1. Calculate historical metrics for the ML Engine
        // For Capstone Demo purposes, we generate dynamic variation so the AI gives different results!
        // In production, these would be fetched from a grading/SIS system.
        $tardiness = $student->attendance()->where('status', 'late')->count();
        
        $totalAbsences = $student->total_absences; // Hits accessor
        
        $behavioralReports = $student->behavioralReports()->count();
        
        $misconduct = $student->behavioralReports()->whereIn('severity', ['moderate', 'severe'])->count();
        
        // Use exact records; default to 0 if no failed subjects tracked yet
        $failedSubjects = 0;

        // 2. Call the Python FastAPI
        try {
            $response = Http::timeout(5)->post($this->apiUrl, [
                'tardiness' => $tardiness,
                'misconduct' => $misconduct,
                'total_absences' => $totalAbsences,
                'behavioral_reports_count' => $behavioralReports,
                'failed_subjects' => $failedSubjects,
                'referral_reason' => $referral->reason
            ]);

            if (!$response->successful()) {
                Log::error("ML API returned error: " . $response->body());
                return false;
            }

            $mlData = $response->json();
            
            // --- STRICT SCHOOL POLICY OVERRIDE ---
            // If the ML is too lenient, we enforce the school's hard rules
            if ($totalAbsences >= 5) {
                $mlData['risk_level'] = 'high';
                $mlData['risk_score'] = max($mlData['risk_score'], rand(80, 95)); // Ensure high score
            } elseif ($totalAbsences >= 3 && $mlData['risk_level'] == 'low') {
                $mlData['risk_level'] = 'moderate';
                $mlData['risk_score'] = max($mlData['risk_score'], rand(50, 65)); // Bump to moderate
            }
            // -------------------------------------

            // 3. Save the Risk Assessment to database
            $assessment = RiskAssessment::create([
                'student_id' => $student->id,
                'tardiness' => $tardiness,
                'misconduct' => $misconduct,
                'total_absences' => $totalAbsences,
                'behavioral_reports_count' => $behavioralReports,
                'failed_subjects' => $failedSubjects,
                'risk_score' => $mlData['risk_score'],
                'risk_level' => $mlData['risk_level'],
                'risk_factors' => json_encode([
                    'reason' => $referral->reason,
                    'recommended_seminar_tag' => $mlData['recommended_seminar_tag'] ?? 'general'
                ]),
                'assessed_at' => now(),
            ]);

            // Update the referral with this assessment ID
            $referral->update([
                'risk_assessment_id' => $assessment->id,
                'priority' => $mlData['risk_level'] == 'high' ? 'high' : ($mlData['risk_level'] == 'moderate' ? 'moderate' : 'low')
            ]);

            // Immediate auto-assignment has been removed.
            // Seminars will be bulk-assigned 1 day prior via Scheduled Job.

            return true;
        } catch (\Exception $e) {
            Log::error("Error during ML risk assessment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run an ML Risk Assessment automatically triggered by attendance or other backend changes.
     */
    public function assessWithoutReferral(Student $student, $reason = "Automated Assessment triggered by backend updates")
    {
        $tardiness = $student->attendance()->where('status', 'late')->count();
        $totalAbsences = $student->total_absences; // Hits accessor
        $behavioralReports = $student->behavioralReports()->count();
        $misconduct = $student->behavioralReports()->whereIn('severity', ['moderate', 'severe'])->count();
        $failedSubjects = 0;

        try {
            $response = Http::timeout(5)->post($this->apiUrl, [
                'tardiness' => $tardiness,
                'misconduct' => $misconduct,
                'total_absences' => $totalAbsences,
                'behavioral_reports_count' => $behavioralReports,
                'failed_subjects' => $failedSubjects,
                'referral_reason' => $reason
            ]);

            if (!$response->successful()) {
                Log::error("ML API returned error: " . $response->body());
                return false;
            }

            $mlData = $response->json();
            
            // --- STRICT SCHOOL POLICY OVERRIDE ---
            if ($totalAbsences >= 5) {
                $mlData['risk_level'] = 'high';
                $mlData['risk_score'] = max($mlData['risk_score'], rand(80, 95));
            } elseif ($totalAbsences >= 3 && $mlData['risk_level'] == 'low') {
                $mlData['risk_level'] = 'moderate';
                $mlData['risk_score'] = max($mlData['risk_score'], rand(50, 65));
            }
            // -------------------------------------

            $assessment = RiskAssessment::create([
                'student_id' => $student->id,
                'tardiness' => $tardiness,
                'misconduct' => $misconduct,
                'total_absences' => $totalAbsences,
                'behavioral_reports_count' => $behavioralReports,
                'failed_subjects' => $failedSubjects,
                'risk_score' => $mlData['risk_score'],
                'risk_level' => $mlData['risk_level'],
                'risk_factors' => json_encode([
                    'reason' => $reason,
                    'recommended_seminar_tag' => $mlData['recommended_seminar_tag'] ?? 'general'
                ]),
                'assessed_at' => now(),
            ]);

            return $assessment;
        } catch (\Exception $e) {
            Log::error("Error during automated ML risk assessment: " . $e->getMessage());
            return false;
        }
    }
}

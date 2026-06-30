<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Referral;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        // Build the base student query scoped to the teacher's advisory settings
        $studentQuery = Student::where('status', 'active');
        
        if (!empty($teacher->handled_course)) {
            $studentQuery->where('course', $teacher->handled_course);
        }
        if (!empty($teacher->handled_grade_level)) {
            $studentQuery->where('grade_level', $teacher->handled_grade_level);
        }
        if (!empty($teacher->handled_section)) {
            $studentQuery->where('section', $teacher->handled_section);
        }

        // Total students the teacher manages
        $totalStudents = (clone $studentQuery)->count();

        // Referrals filed by this teacher
        $myReferrals = Referral::where('referred_by', auth()->id())->count();
        $myPendingReferrals = Referral::where('referred_by', auth()->id())
                                      ->where('status', 'pending')->count();

        // Fetch the absence threshold from settings, default to 3 if not found
        $thresholdSetting = Setting::get('absence_warning_threshold', 3);
        $threshold = (int) $thresholdSetting;

        // Find students who have reached or exceeded the threshold
        $atRiskStudents = (clone $studentQuery)
            ->withCount(['attendance as total_absences' => function($query) {
                $query->where('status', 'absent');
            }])
            ->having('total_absences', '>=', $threshold)
            ->orderBy('total_absences', 'desc')
            ->take(5)
            ->get();

        // Check if any of these at-risk students already have an active referral
        foreach ($atRiskStudents as $student) {
            $student->has_active_referral = Referral::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();
        }

        return view('teacher.dashboard', compact(
            'totalStudents', 
            'myReferrals', 
            'myPendingReferrals', 
            'atRiskStudents', 
            'threshold'
        ));
    }
}

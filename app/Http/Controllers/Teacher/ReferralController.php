<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Teacher only sees referrals they filed
        $referrals = Referral::with(['student', 'counselor'])
            ->where('referred_by', auth()->id())
            ->latest()
            ->paginate(15);
            
        $pendingCount = Referral::where('referred_by', auth()->id())->where('status', 'pending')->count();

        return view('teacher.referrals.index', compact('referrals', 'pendingCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
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

        // Get active students to refer
        $students = $studentQuery->orderBy('last_name')->get();
        // If a specific student was passed via query parameter (e.g. from attendance warning)
        $selectedStudentId = $request->query('student_id');

        return view('teacher.referrals.create', compact('students', 'selectedStudentId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, SmsService $smsService, \App\Services\RiskAssessmentService $riskService)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'referral_type' => 'required|string|max:100',
            'reason'        => 'required|string',
        ]);

        $referral = Referral::create([
            'student_id'    => $request->student_id,
            'referred_by'   => auth()->id(),
            'referral_type' => $request->referral_type,
            'reason'        => $request->reason,
            // Priority is initially pending ML evaluation, but we set a default
            'priority'      => 'moderate',
            'status'        => 'pending',
        ]);
        
        $student = Student::find($request->student_id);

        // TRIGGER ML RISK ASSESSMENT & AUTO-SEMINAR ASSIGNMENT
        // This runs the python API, creates RiskAssessment, assigns Seminar, and sends Seminar SMS
        $riskService->assessAndAssignSeminar($student, $referral);

        // TRIGGER SMS TO PARENT (Referral Notification)
        if (!empty($student->parent_contact)) {
            $message = "MU Guidance: Your child {$student->first_name} has been referred to the guidance office by their teacher. Reason: {$request->referral_type}.";
            
            $smsService->sendSms(
                $student->parent_contact,
                $message,
                $student->id,
                $student->parent_name ?? 'Parent',
                'parent',
                $referral->id
            );
        }

        return redirect()->route('teacher.referrals.index')
            ->with('success', 'Referral submitted. The AI has automatically assessed the student and assigned an intervention if required.');
    }
}

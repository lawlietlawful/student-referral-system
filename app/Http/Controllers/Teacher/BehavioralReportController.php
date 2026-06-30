<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BehavioralReport;
use App\Models\Student;
use App\Services\SmsService;

class BehavioralReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = BehavioralReport::with('student')
                    ->where('reported_by', auth()->id())
                    ->latest()
                    ->paginate(15);
                    
        return view('teacher.behavioral-reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('last_name')->get();
        return view('teacher.behavioral-reports.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, SmsService $smsService)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'incident_type' => 'required|string|max:100', // e.g. Failing Grade, Truancy, Disciplinary
            'severity' => 'required|in:Low,Medium,High,Critical',
            'incident_date' => 'required|date',
            'location' => 'nullable|string|max:100',
            'description' => 'required|string',
        ]);

        $report = BehavioralReport::create([
            'student_id' => $request->student_id,
            'reported_by' => auth()->id(),
            'incident_type' => $request->incident_type,
            'severity' => $request->severity,
            'incident_date' => $request->incident_date,
            'location' => $request->location,
            'description' => $request->description,
            'status' => 'pending', // Awaiting guidance action
        ]);

        // If the incident is High or Critical, or it's a Academic Failure, trigger an SMS
        $criticalTypes = ['Academic Failure', 'Failing Grade'];
        if ($request->severity === 'High' || $request->severity === 'Critical' || in_array($request->incident_type, $criticalTypes)) {
            $student = Student::find($request->student_id);
            
            $message = "MU Advisory: Your child, {$student->first_name} {$student->last_name}, has been flagged for '{$request->incident_type}' (Severity: {$request->severity}). Please contact the Guidance Office immediately.";
            
            if ($student->parent_contact) {
                $smsService->sendSms(
                    $student->parent_contact,
                    $message,
                    $student->id,
                    $student->parent_name,
                    'parent'
                );
            }
        }

        return redirect()->route('teacher.behavioral-reports.index')
            ->with('success', 'Report submitted successfully. Parent notified if critical.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BehavioralReport $behavioral_report)
    {
        // Must authorize that the teacher owns this report
        if ($behavioral_report->reported_by !== auth()->id()) {
            abort(403);
        }
        
        return view('teacher.behavioral-reports.show', compact('behavioral_report'));
    }
}

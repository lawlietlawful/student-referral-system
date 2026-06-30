<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();
        
        $date = $request->get('date', date('Y-m-d'));
        
        // If teacher has assigned advisory, enforce it. Otherwise, allow manual filter.
        $course = $teacher->handled_course ?: $request->get('course');
        $section = $teacher->handled_section ?: $request->get('section');
        $grade_level = $teacher->handled_grade_level ?: $request->get('grade_level');
        
        $query = Student::query()->where('status', 'active');
        
        if ($course) {
            $query->where('course', $course);
        }
        if ($section) {
            $query->where('section', $section);
        }
        if ($grade_level) {
            $query->where('grade_level', $grade_level);
        }

        // Get distinct courses and sections for the filter dropdowns (scoped to teacher if applicable)
        $courses = Student::select('course');
        if($teacher->handled_course) $courses->where('course', $teacher->handled_course);
        $courses = $courses->distinct()->pluck('course');

        $sections = Student::select('section');
        if($teacher->handled_section) $sections->where('section', $teacher->handled_section);
        $sections = $sections->distinct()->pluck('section');

        $students = $query->orderBy('last_name')->get();

        // Get existing attendance for the selected date
        $existingAttendance = Attendance::where('teacher_id', auth()->id())
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('teacher.attendance.index', compact('students', 'date', 'course', 'section', 'courses', 'sections', 'existingAttendance'));
    }

    /**
     * Store bulk attendance and trigger SMS if threshold is met.
     */
    public function bulkStore(Request $request, SmsService $smsService, \App\Services\RiskAssessmentService $riskService)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late',
            'attendance.*.absence_type' => 'nullable|in:excused,unexcused',
            'attendance.*.remarks' => 'nullable|string'
        ]);

        $date = Carbon::parse($request->date)->format('Y-m-d');
        $teacherId = auth()->id();
        $absentThreshold = 3; // Number of absences to trigger SMS

        try {
            DB::beginTransaction();

            foreach ($request->attendance as $studentId => $data) {
                // Update or Create the attendance record
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'teacher_id' => $teacherId,
                        'date' => $date
                    ],
                    [
                        'status' => $data['status'],
                        'absence_type' => $data['status'] === 'absent' ? ($data['absence_type'] ?? 'unexcused') : null,
                        'remarks' => $data['remarks'] ?? null
                    ]
                );

                // If marked absent, check total absences
                if ($data['status'] === 'absent') {
                    $student = Student::find($studentId);
                    $totalAbsences = $student->total_absences; // Uses the helper in Student model

                    // If total absences reaches threshold (e.g., exactly 3 or 5)
                    if ($totalAbsences == 3 || $totalAbsences == 5) {
                        $message = "MU Advisory: Your child, {$student->first_name} {$student->last_name}, has accumulated {$totalAbsences} absences. Please contact the Guidance Office immediately to discuss this matter.";
                        
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
                }
                
                // Trigger automated ML Risk Assessment on Late or Absent
                if (in_array($data['status'], ['absent', 'late'])) {
                    $studentToAssess = isset($student) ? $student : Student::find($studentId);
                    $riskService->assessWithoutReferral($studentToAssess, "Automated Assessment triggered by recent Attendance update");
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Attendance saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $course = $request->query('course');
        $section = $request->query('section');

        $query = Student::where('status', 'active');
        if ($course) $query->where('course', $course);
        if ($section) $query->where('section', $section);

        $students = $query->orderBy('last_name')->get();
        $studentIds = $students->pluck('id')->toArray();

        $existingAttendance = Attendance::whereIn('student_id', $studentIds)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        $csvData = [];
        $csvData[] = ['Student ID', 'Last Name', 'First Name', 'Course', 'Section', 'Date', 'Status', 'Absence Type', 'Remarks'];

        foreach ($students as $student) {
            $att = $existingAttendance->get($student->id);
            $csvData[] = [
                $student->student_id_number,
                $student->last_name,
                $student->first_name,
                $student->course,
                $student->section,
                $date,
                $att ? ucfirst($att->status) : 'Present',
                $att && $att->status === 'absent' ? ucfirst($att->absence_type ?? 'Unexcused') : 'N/A',
                $att ? $att->remarks : ''
            ];
        }

        $filename = "attendance_{$date}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

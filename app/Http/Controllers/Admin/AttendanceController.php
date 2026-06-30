<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $course = $request->get('course');
        $gradeLevel = $request->get('grade_level');
        $section = $request->get('section');
        $status = $request->get('status');
        $search = $request->get('search');

        // Build the attendance query for the selected date range
        $query = Attendance::with(['student', 'teacher'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($course) {
            $query->whereHas('student', function ($q) use ($course) {
                $q->where('course', $course);
            });
        }

        if ($gradeLevel) {
            $query->whereHas('student', function ($q) use ($gradeLevel) {
                $q->where('grade_level', $gradeLevel);
            });
        }

        if ($section) {
            $query->whereHas('student', fn($q) => $q->where('section', $section));
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id_number', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        // Summary stats for the selected date range
        $presentCount = Attendance::whereBetween('date', [$startDate, $endDate])->where('status', 'present')->count();
        $absentCount  = Attendance::whereBetween('date', [$startDate, $endDate])->where('status', 'absent')->count();
        $lateCount    = Attendance::whereBetween('date', [$startDate, $endDate])->where('status', 'late')->count();
        $excusedCount = Attendance::whereBetween('date', [$startDate, $endDate])->where('status', 'excused')->count();

        $topAbsentees = Student::where('status', 'active')
            ->withCount(['attendance as absence_count' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])->where('status', 'absent');
            }])
            ->having('absence_count', '>', 0)
            ->orderByDesc('absence_count')
            ->limit(10)
            ->get();

        // Calculate attendance rate
        $totalCount = $presentCount + $absentCount + $lateCount + $excusedCount;
        $attendanceRate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;

        // Filter dropdowns
        $courses = Student::select('course')->whereNotNull('course')->distinct()->orderBy('course')->pluck('course');
        $sections = Student::select('section')->distinct()->orderBy('section')->pluck('section');

        return view('admin.attendance.index', compact(
            'records', 'startDate', 'endDate', 'presentCount', 'absentCount', 'lateCount', 'excusedCount',
            'topAbsentees', 'courses', 'sections', 'attendanceRate', 'totalCount'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        
        $query = Attendance::with(['student', 'teacher'])->whereBetween('date', [$startDate, $endDate]);
        
        if ($request->status) $query->where('status', $request->status);
        if ($request->course) $query->whereHas('student', fn($q) => $q->where('course', $request->course));
        if ($request->grade_level) $query->whereHas('student', fn($q) => $q->where('grade_level', $request->grade_level));
        if ($request->section) $query->whereHas('student', fn($q) => $q->where('section', $request->section));
        
        $records = $query->orderBy('date', 'desc')->get();
        $fileName = 'attendance_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Student ID', 'Last Name', 'First Name', 'Course/Section', 'Status', 'Recorded By', 'Remarks'];

        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($records as $record) {
                $row = [
                    Carbon::parse($record->date)->format('M d, Y'),
                    $record->student->student_id_number ?? 'N/A',
                    $record->student->last_name,
                    $record->student->first_name,
                    ($record->student->course ?? $record->student->grade_level) . ' - ' . $record->student->section,
                    strtoupper($record->status),
                    $record->teacher->name ?? 'Admin',
                    $record->remarks ?? ''
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sendWarningSms(Student $student, \App\Services\SmsService $smsService)
    {
        $absenceCount = Attendance::where('student_id', $student->id)->where('status', 'absent')->count();
        
        if ($student->parent_contact) {
            $msg = "MU Advisory: Your child, {$student->first_name}, has accumulated {$absenceCount} unexcused absences. Please contact the Guidance Office immediately to discuss this matter.";
            $smsService->sendSms($student->parent_contact, $msg, $student->id, $student->parent_name, 'parent');
            return back()->with('error', 'Failed to send SMS warning: ' . $e->getMessage());
        }

        return redirect()->back()->with('error', 'Student does not have a registered parent contact number.');
    }

    /**
     * Send bulk SMS warnings to multiple students.
     */
    public function bulkSmsWarning(Request $request, \App\Services\SmsService $smsService)
    {
        $request->validate([
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id'
        ]);

        $attendances = Attendance::whereIn('id', $request->attendance_ids)->with('student')->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($attendances as $attendance) {
            $student = $attendance->student;

            if ($student && $student->parent_contact && preg_match('/^(09|\+639)\d{9}$/', $student->parent_contact)) {
                try {
                    $message = "URGENT: Your child, {$student->first_name} {$student->last_name}, was marked ABSENT today (" . \Carbon\Carbon::parse($attendance->date)->format('M d') . "). Please contact the school immediately.";
                    $smsService->sendSms($student->parent_contact, $message, $student->id, $student->parent_name, 'parent');
                    $sentCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            $msg = "Successfully sent {$sentCount} SMS warnings.";
            if ($failedCount > 0) {
                $msg .= " Failed to send to {$failedCount} parents (missing or invalid contact).";
            }
            return back()->with('success', $msg);
        }

        return back()->with('error', 'Failed to send any SMS warnings. Please check if the students have valid parent contact numbers.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BehavioralReport;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher');

        // Search by name, email, or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Handle sorting
        $sort = $request->input('sort', 'name_asc');
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        // Eager-load counts for activity stats
        $query->withCount([
            'behavioralReports',
        ]);

        $teachers = $query->paginate(15)->appends($request->query());

        // Attach attendance record counts manually (since User doesn't have direct relationship)
        $teacherIds = $teachers->pluck('id');
        $attendanceData = Attendance::select('teacher_id', DB::raw('count(*) as total'), DB::raw('max(date) as last_date'))
            ->whereIn('teacher_id', $teacherIds)
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        $now = now();
        foreach ($teachers as $teacher) {
            $data = $attendanceData[$teacher->id] ?? null;
            $teacher->attendance_records_count = $data ? $data->total : 0;
            
            if ($data && $data->last_date) {
                $lastDate = \Carbon\Carbon::parse($data->last_date);
                $daysSince = $lastDate->diffInDays($now);
                
                if ($daysSince <= 2) {
                    $teacher->engagement_status = 'highly_active';
                } elseif ($daysSince <= 7) {
                    $teacher->engagement_status = 'active';
                } else {
                    $teacher->engagement_status = 'inactive';
                }
            } else {
                $teacher->engagement_status = 'inactive';
            }
        }

        // Summary stats
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalAttendanceRecords = Attendance::count();
        $totalBehavioralReports = BehavioralReport::count();

        return view('admin.teachers.index', compact(
            'teachers', 'totalTeachers', 'totalAttendanceRecords', 'totalBehavioralReports', 'attendanceData'
        ));
    }

    public function show(User $teacher)
    {
        // Ensure only teacher profiles are viewable here
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        // Recent attendance records filed by this teacher
        $recentAttendance = Attendance::with('student')
            ->where('teacher_id', $teacher->id)
            ->latest('date')
            ->take(15)
            ->get();

        // Recent behavioral reports filed by this teacher
        $recentReports = BehavioralReport::with('student')
            ->where('reported_by', $teacher->id)
            ->latest()
            ->take(10)
            ->get();

        // Stats
        $totalAttendance = Attendance::where('teacher_id', $teacher->id)->count();
        $totalReports = BehavioralReport::where('reported_by', $teacher->id)->count();
        $totalAbsentsMarked = Attendance::where('teacher_id', $teacher->id)->where('status', 'absent')->count();

        // Attendance by date (last 30 days this teacher logged)
        $last30Days = collect();
        $today = now();
        for ($i = 29; $i >= 0; $i--) {
            $last30Days->put($today->copy()->subDays($i)->format('Y-m-d'), 0);
        }

        $attendanceLogs = Attendance::where('teacher_id', $teacher->id)
            ->where('date', '>=', now()->subDays(30)->format('Y-m-d'))
            ->select('date', DB::raw('count(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Merge actual logs with the 30-day scaffold
        $attendanceDates = $last30Days->merge($attendanceLogs);

        // Calculate Engagement Status based on last logged date
        $latestAttendance = Attendance::where('teacher_id', $teacher->id)->max('date');
        $teacher->engagement_status = 'inactive';
        if ($latestAttendance) {
            $daysSince = \Carbon\Carbon::parse($latestAttendance)->diffInDays(now());
            if ($daysSince <= 2) {
                $teacher->engagement_status = 'highly_active';
            } elseif ($daysSince <= 7) {
                $teacher->engagement_status = 'active';
            }
        }

        return view('admin.teachers.show', compact(
            'teacher', 'recentAttendance', 'recentReports',
            'totalAttendance', 'totalReports', 'totalAbsentsMarked', 'attendanceDates'
        ));
    }

    public function print(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $totalAttendance = Attendance::where('teacher_id', $teacher->id)->count();
        $totalReports = BehavioralReport::where('reported_by', $teacher->id)->count();
        $totalAbsentsMarked = Attendance::where('teacher_id', $teacher->id)->where('status', 'absent')->count();

        $recentAttendance = Attendance::with('student')
            ->where('teacher_id', $teacher->id)
            ->latest('date')
            ->take(50)
            ->get();

        $recentReports = BehavioralReport::with('student')
            ->where('reported_by', $teacher->id)
            ->latest()
            ->take(50)
            ->get();
            
        $latestAttendance = Attendance::where('teacher_id', $teacher->id)->max('date');
        $engagementStatus = 'Inactive';
        if ($latestAttendance) {
            $daysSince = \Carbon\Carbon::parse($latestAttendance)->diffInDays(now());
            if ($daysSince <= 2) $engagementStatus = 'Highly Active';
            elseif ($daysSince <= 7) $engagementStatus = 'Active';
        }

        return view('admin.teachers.print', compact(
            'teacher', 'totalAttendance', 'totalReports', 'totalAbsentsMarked', 
            'recentAttendance', 'recentReports', 'engagementStatus', 'latestAttendance'
        ));
    }

    public function export()
    {
        $teachers = User::where('role', 'teacher')->get();

        $teacherIds = $teachers->pluck('id');
        
        $attendanceData = Attendance::select('teacher_id', DB::raw('count(*) as total'), DB::raw('max(date) as last_date'))
            ->whereIn('teacher_id', $teacherIds)
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');
            
        $reportsCount = BehavioralReport::select('reported_by', DB::raw('count(*) as total'))
            ->whereIn('reported_by', $teacherIds)
            ->groupBy('reported_by')
            ->pluck('total', 'reported_by');

        $filename = "teacher_directory_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Teacher Name', 'Email', 'Username', 'Attendance Filed', 'Reports Filed', 'Engagement Status', 'Joined Date');

        $callback = function() use($teachers, $columns, $attendanceData, $reportsCount) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            $now = now();
            
            foreach ($teachers as $teacher) {
                $attData = $attendanceData[$teacher->id] ?? null;
                $attendanceFiled = $attData ? $attData->total : 0;
                $repFiled = $reportsCount[$teacher->id] ?? 0;
                
                $status = 'Inactive';
                if ($attData && $attData->last_date) {
                    $daysSince = \Carbon\Carbon::parse($attData->last_date)->diffInDays($now);
                    if ($daysSince <= 2) $status = 'Highly Active';
                    elseif ($daysSince <= 7) $status = 'Active';
                }
                
                fputcsv($file, array(
                    $teacher->name,
                    $teacher->email,
                    $teacher->username ?? 'N/A',
                    $attendanceFiled,
                    $repFiled,
                    $status,
                    $teacher->created_at->format('Y-m-d')
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

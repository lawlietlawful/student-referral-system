<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BehavioralReport;
use App\Models\Student;

class BehavioralReportController extends Controller
{
    public function index(Request $request)
    {
        $query = BehavioralReport::with(['student', 'reportedBy'])->latest();

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by incident type
        if ($request->filled('incident_type')) {
            $query->where('incident_type', $request->incident_type);
        }

        // Search by student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id_number', 'like', "%{$search}%");
            });
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('incident_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('incident_date', '<=', $request->date_to);
        }

        $reports = $query->paginate(20)->appends($request->query());

        // Summary stats
        $totalReports   = BehavioralReport::count();
        $pendingCount   = BehavioralReport::where('status', 'pending')->count();
        $reviewedCount  = BehavioralReport::where('status', 'reviewed')->count();
        $resolvedCount  = BehavioralReport::where('status', 'resolved')->count();

        // Get distinct incident types for filter dropdown
        $incidentTypes = BehavioralReport::select('incident_type')
            ->distinct()
            ->orderBy('incident_type')
            ->pluck('incident_type');

        return view('admin.behavioral-reports.index', compact(
            'reports', 'totalReports', 'pendingCount', 'reviewedCount', 'resolvedCount', 'incidentTypes'
        ));
    }

    public function show(BehavioralReport $behavioral_report)
    {
        $behavioral_report->load(['student', 'reportedBy']);

        return view('admin.behavioral-reports.show', compact('behavioral_report'));
    }

    public function updateStatus(Request $request, BehavioralReport $behavioral_report)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
        ]);

        $behavioral_report->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Report status updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Referral::with(['student', 'referredBy', 'counselor', 'riskAssessment'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
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

        $referrals = $query->paginate(15)->appends($request->query());

        // Summary counts
        $pendingCount    = Referral::where('status', 'pending')->count();
        $inProgressCount = Referral::where('status', 'in_progress')->count();
        $resolvedCount   = Referral::where('status', 'resolved')->count();
        $totalCount      = Referral::count();

        return view('counselor.referrals.index', compact(
            'referrals', 'pendingCount', 'inProgressCount', 'resolvedCount', 'totalCount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('last_name')->get();
        $counselors = User::where('role', 'guidance_counselor')->orderBy('name')->get();

        return view('counselor.referrals.create', compact('students', 'counselors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'referral_type' => 'required|string|max:100',
            'reason'        => 'required|string',
            'priority'      => 'required|in:low,moderate,high',
            'counselor_id'  => 'nullable|exists:users,id',
        ]);

        Referral::create([
            'student_id'    => $request->student_id,
            'referred_by'   => auth()->id(),
            'counselor_id'  => $request->counselor_id,
            'referral_type' => $request->referral_type,
            'reason'        => $request->reason,
            'priority'      => $request->priority,
            'status'        => 'pending',
        ]);

        return redirect()->route('counselor.referrals.index')
            ->with('success', 'Referral submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Referral $referral)
    {
        $referral->load(['student', 'referredBy', 'counselor', 'interventions', 'smsLogs', 'riskAssessment']);
        
        $counselors = User::where('role', 'guidance_counselor')->orderBy('name')->get();

        // Get AI Recommended Seminars if applicable
        $recommendedSeminars = [];
        if ($referral->riskAssessment) {
            $factors = json_decode($referral->riskAssessment->risk_factors, true);
            $tag = $factors['recommended_seminar_tag'] ?? null;
            if ($tag) {
                $recommendedSeminars = \App\Models\Seminar::where('trigger_reason', $tag)
                    ->whereIn('status', ['upcoming', 'ongoing'])
                    ->get();
            }
        }

        // Build Visual Timeline
        $timeline = collect();

        // 1. Referral Created
        $timeline->push([
            'type' => 'referral_created',
            'date' => $referral->created_at,
            'title' => 'Referral Filed',
            'description' => "Filed by {$referral->referredBy->name} ({$referral->referral_type})",
            'icon' => 'ti-file-pencil',
            'color' => 'bg-blue-100 text-blue-600',
        ]);

        // 2. AI Assessment
        if ($referral->riskAssessment) {
            $aiClass = match($referral->riskAssessment->risk_level) {
                'high' => 'bg-red-100 text-red-600',
                'moderate' => 'bg-yellow-100 text-yellow-600',
                default => 'bg-green-100 text-green-600',
            };
            $timeline->push([
                'type' => 'ai_assessment',
                'date' => $referral->riskAssessment->assessed_at ?? $referral->riskAssessment->created_at,
                'title' => 'AI Risk Assessment',
                'description' => "Flagged as " . ucfirst($referral->riskAssessment->risk_level) . " Risk ({$referral->riskAssessment->risk_score}%)",
                'icon' => 'ti-brain',
                'color' => $aiClass,
            ]);
        }

        // 3. Interventions
        foreach ($referral->interventions as $intervention) {
            $timeline->push([
                'type' => 'intervention',
                'date' => $intervention->created_at,
                'title' => 'Intervention Logged',
                'description' => "{$intervention->intervention_type} - Outcome: " . ucfirst($intervention->outcome ?? 'Pending'),
                'icon' => 'ti-heart-handshake',
                'color' => 'bg-indigo-100 text-indigo-600',
                'id' => $intervention->id
            ]);
        }

        // 4. SMS Notifications
        foreach ($referral->smsLogs as $sms) {
            $timeline->push([
                'type' => 'sms',
                'date' => $sms->created_at,
                'title' => 'SMS Notification',
                'description' => "Sent to {$sms->recipient_name}: '{$sms->message}'",
                'icon' => 'ti-message-share',
                'color' => $sms->status === 'sent' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600',
            ]);
        }

        // 5. Parent Communications
        $referral->load('parentCommunications');
        foreach ($referral->parentCommunications as $log) {
            $timeline->push([
                'type' => 'parent_contact',
                'date' => $log->created_at,
                'title' => 'Parent Contact Logged',
                'description' => "Method: " . ucfirst($log->contact_method) . " - " . $log->summary,
                'icon' => 'ti-headset',
                'color' => 'bg-purple-100 text-purple-600',
            ]);
        }

        // 6. Resolved
        if ($referral->resolved_at) {
            $timeline->push([
                'type' => 'resolved',
                'date' => $referral->resolved_at,
                'title' => 'Case Resolved',
                'description' => 'The referral was officially closed.',
                'icon' => 'ti-circle-check',
                'color' => 'bg-emerald-100 text-emerald-600',
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->values();

        return view('counselor.referrals.show', compact('referral', 'counselors', 'recommendedSeminars', 'timeline'));
    }

    /**
     * Update the referral status (used by admin to assign counselor or change status).
     */
    public function updateStatus(Request $request, Referral $referral)
    {
        $request->validate([
            'status'          => 'required|in:pending,in_progress,resolved,cancelled',
            'counselor_id'    => 'nullable|exists:users,id',
            'counselor_notes' => 'nullable|string',
        ]);

        $data = [
            'status'          => $request->status,
            'counselor_notes' => $request->counselor_notes,
        ];

        if ($request->filled('counselor_id')) {
            $data['counselor_id'] = $request->counselor_id;
        }

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
        }

        $referral->update($data);

        return redirect()->back()->with('success', 'Referral status updated successfully.');
    }

    /**
     * Display a printable version of the referral case report.
     */
    public function print(Referral $referral)
    {
        $referral->load(['student.attendance', 'student.behavioralReports', 'referredBy', 'counselor', 'interventions', 'smsLogs', 'riskAssessment']);
        return view('counselor.referrals.print', compact('referral'));
    }

    /**
     * Log a parent communication for the referral.
     */
    public function logParentContact(Request $request, Referral $referral)
    {
        $request->validate([
            'contact_method' => 'required|in:call,sms,email,visit,other',
            'summary' => 'required|string|max:1000',
        ]);

        $referral->parentCommunications()->create([
            'contact_method' => $request->contact_method,
            'summary' => $request->summary,
        ]);

        return redirect()->back()->with('success', 'Parent communication logged successfully.');
    }
}

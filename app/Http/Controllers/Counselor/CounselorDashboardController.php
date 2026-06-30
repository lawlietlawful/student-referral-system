<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\Intervention;
use App\Models\Seminar;
use Carbon\Carbon;

class CounselorDashboardController extends Controller
{
    public function index()
    {
        // 1. Pending Referrals
        $pendingReferralsCount = Referral::where('status', 'pending')->count();
        $recentPendingReferrals = Referral::with(['student', 'referredBy'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // 2. Upcoming Follow-ups (instead of Scheduled Interventions)
        $upcomingInterventionsCount = Intervention::whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', Carbon::today())
            ->count();
            
        $upcomingInterventions = Intervention::with('referral.student')
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', Carbon::today())
            ->orderBy('follow_up_date')
            ->take(5)
            ->get();

        // 3. Active Seminars
        $activeSeminarsCount = Seminar::where('status', 'upcoming')
            ->orWhere('status', 'ongoing')
            ->count();
        $upcomingSeminars = Seminar::where('status', 'upcoming')
            ->orWhere('status', 'ongoing')
            ->orderBy('date')
            ->take(3)
            ->get();

        // 4. Quick stat: Total interventions completed this month
        // We'll consider it "completed" if it has an outcome or was logged this month
        $completedInterventionsThisMonth = Intervention::whereNotNull('outcome')
            ->whereMonth('intervention_date', Carbon::now()->month)
            ->whereYear('intervention_date', Carbon::now()->year)
            ->count();

        return view('counselor.dashboard.index', compact(
            'pendingReferralsCount', 'recentPendingReferrals',
            'upcomingInterventionsCount', 'upcomingInterventions',
            'activeSeminarsCount', 'upcomingSeminars',
            'completedInterventionsThisMonth'
        ));
    }
}

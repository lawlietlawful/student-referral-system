@extends('layouts.admin')

@section('title', 'Admin Overview')
@section('page-title', 'Admin Overview')
@section('page-sub', 'S.Y. 2025–2026 · Second Semester')

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-3 mb-5">

    {{-- Total Students --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
            <i class="ti ti-users text-blue-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-gray-900">{{ number_format($totalStudents) }}</div>
        <div class="text-xs text-gray-400">Total Students</div>
        <div class="text-[11px] text-green-600 flex items-center gap-1">
            <i class="ti ti-trending-up text-xs"></i>
            {{ $newStudentsThisWeek }} enrolled this week
        </div>
    </div>

    {{-- At-Risk Students --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
            <i class="ti ti-alert-circle text-red-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-gray-900">{{ number_format($atRiskCount) }}</div>
        <div class="text-xs text-gray-400">At-Risk Students</div>
        <div class="text-[11px] text-red-500 flex items-center gap-1">
            <i class="ti ti-trending-up text-xs"></i>
            {{ $newFlagsToday }} new flags today
        </div>
    </div>

    {{-- Pending Referrals --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
            <i class="ti ti-file-text text-amber-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-gray-900">{{ number_format($pendingReferrals) }}</div>
        <div class="text-xs text-gray-400">Pending Referrals</div>
        <div class="text-[11px] text-amber-600 flex items-center gap-1">
            <i class="ti ti-clock text-xs"></i>
            {{ $awaitingAction }} awaiting action
        </div>
    </div>

    {{-- SMS Sent --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
            <i class="ti ti-message text-green-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-gray-900">{{ number_format($smsSentThisMonth) }}</div>
        <div class="text-xs text-gray-400">SMS Sent This Month</div>
        <div class="text-[11px] text-green-600 flex items-center gap-1">
            <i class="ti ti-trending-up text-xs"></i>
            {{ $smsDeliveryRate }}% delivery rate
        </div>
    </div>

</div>

{{-- ── Main Row: Referrals Table + Right Column ────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-4">

    {{-- Recent Referrals Table --}}
    <div class="col-span-2 bg-white border border-gray-100 rounded-xl p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-medium text-gray-900">Recent referrals</h2>
            <a href="{{ route('admin.referrals.index') }}"
               class="text-xs text-blue-600 hover:underline">View all</a>
        </div>

        <table class="w-full text-sm table-fixed">
            <thead>
                <tr>
                    <th class="text-left text-[11px] text-gray-400 font-medium pb-2 border-b border-gray-100 w-[28%]">Student</th>
                    <th class="text-left text-[11px] text-gray-400 font-medium pb-2 border-b border-gray-100 w-[22%]">Grade & Section</th>
                    <th class="text-left text-[11px] text-gray-400 font-medium pb-2 border-b border-gray-100 w-[22%]">Reason</th>
                    <th class="text-left text-[11px] text-gray-400 font-medium pb-2 border-b border-gray-100 w-[14%]">Risk</th>
                    <th class="text-left text-[11px] text-gray-400 font-medium pb-2 border-b border-gray-100 w-[14%]">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReferrals as $referral)
                <tr class="border-b border-gray-50 last:border-0">
                    <td class="py-2.5 text-xs font-medium text-gray-800 truncate">
                        {{ $referral->student->full_name }}
                    </td>
                    <td class="py-2.5 text-xs text-gray-500 truncate">
                        Grade {{ $referral->student->grade_level }} — {{ $referral->student->section }}
                    </td>
                    <td class="py-2.5 text-xs text-gray-500 truncate">
                        {{ $referral->referral_type }}
                    </td>
                    <td class="py-2.5">
                        @php
                            $riskClass = match($referral->priority) {
                                'high'     => 'bg-red-50 text-red-600',
                                'moderate' => 'bg-amber-50 text-amber-600',
                                default    => 'bg-green-50 text-green-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                     font-medium {{ $riskClass }}">
                            {{ ucfirst($referral->priority) }}
                        </span>
                    </td>
                    <td class="py-2.5">
                        @php
                            $statusClass = match($referral->status) {
                                'pending'     => 'bg-blue-50 text-blue-600',
                                'in_progress' => 'bg-orange-50 text-orange-600',
                                'resolved'    => 'bg-green-50 text-green-600',
                                default       => 'bg-gray-100 text-gray-500',
                            };
                            $statusLabel = match($referral->status) {
                                'pending'     => 'Pending',
                                'in_progress' => 'In Progress',
                                'resolved'    => 'Resolved',
                                'cancelled'   => 'Cancelled',
                                default       => ucfirst($referral->status),
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                     font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-xs text-gray-400">
                        <i class="ti ti-file-off text-2xl block mb-1"></i>
                        No referrals yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Right Column --}}
    <div class="flex flex-col gap-4">

        {{-- Risk Distribution --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <h2 class="text-sm font-medium text-gray-900 mb-3">Student risk distribution</h2>

            {{-- Segmented bar --}}
            <div class="flex h-2 rounded-full overflow-hidden gap-0.5 mb-3">
                <div class="risk-bar-low rounded-full"
                     style="width: {{ $riskDistribution['low_pct'] }}%"></div>
                <div class="risk-bar-mod rounded-full"
                     style="width: {{ $riskDistribution['moderate_pct'] }}%"></div>
                <div class="risk-bar-high rounded-full"
                     style="width: {{ $riskDistribution['high_pct'] }}%"></div>
            </div>

            <div class="flex gap-4">
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-green-600 inline-block"></span>
                    Low
                    <span class="font-medium text-gray-800">{{ $riskDistribution['low'] }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                    Moderate
                    <span class="font-medium text-gray-800">{{ $riskDistribution['moderate'] }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-red-600 inline-block"></span>
                    High
                    <span class="font-medium text-gray-800">{{ $riskDistribution['high'] }}</span>
                </div>
            </div>
        </div>

        {{-- Upcoming Seminars --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-medium text-gray-900">Upcoming seminars</h2>
                <a href="{{ route('admin.seminars.index') }}"
                   class="text-xs text-blue-600 hover:underline">Manage</a>
            </div>

            @forelse($upcomingSeminars as $seminar)
            <div class="flex items-center gap-2.5 py-2
                        {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center
                            justify-content-center justify-center flex-shrink-0">
                    <i class="ti ti-school text-blue-600 text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $seminar->title }}</p>
                    <p class="text-[11px] text-gray-400">
                        {{ $seminar->date->format('M d') }} · {{ \Carbon\Carbon::parse($seminar->time)->format('g:i A') }}
                    </p>
                </div>
                <span class="flex-shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-full
                             {{ $seminar->is_required
                                ? 'bg-red-50 text-red-600'
                                : 'bg-green-50 text-green-600' }}">
                    {{ $seminar->is_required ? 'Required' : 'Optional' }}
                </span>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">No upcoming seminars.</p>
            @endforelse
        </div>

    </div>
</div>

{{-- ── Bottom Row: Chart + Activity Feed ──────────────────── --}}
<div class="grid grid-cols-2 gap-4">

    {{-- Monthly Referral Trend Chart --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <h2 class="text-sm font-medium text-gray-900 mb-4">Monthly referral trend</h2>

        <div class="flex gap-4 mb-3">
            <span class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span>
                Referrals
            </span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="w-2.5 h-2.5 rounded-sm bg-green-600 inline-block"></span>
                Resolved
            </span>
        </div>

        <div style="position: relative; width: 100%; height: 200px;">
            <canvas id="referralChart"
                    role="img"
                    aria-label="Bar chart showing monthly referrals vs resolved from January to June">
                Monthly referrals and resolved counts per month.
            </canvas>
        </div>
    </div>

    {{-- Recent Activity Feed --}}
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <h2 class="text-sm font-medium text-gray-900 mb-3">Recent activity</h2>

        <div class="flex flex-col">
            @forelse($recentActivities as $activity)
            <div class="flex gap-2.5 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                @php
                    $dotColor = match($activity['type']) {
                        'risk'         => 'bg-red-500',
                        'sms'          => 'bg-blue-500',
                        'resolved'     => 'bg-green-500',
                        'behavioral'   => 'bg-amber-500',
                        'seminar'      => 'bg-blue-500',
                        default        => 'bg-gray-400',
                    };
                @endphp
                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $dotColor }}"></div>
                <div>
                    <p class="text-xs text-gray-700 leading-snug">{!! $activity['message'] !!}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $activity['time'] }}</p>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-6">No recent activity.</p>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    const referralData  = @json($monthlyReferrals);
    const resolvedData  = @json($monthlyResolved);
    const monthLabels   = @json($monthLabels);

    new Chart(document.getElementById('referralChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Referrals',
                    data: referralData,
                    backgroundColor: '#2563EB',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Resolved',
                    data: resolvedData,
                    backgroundColor: '#16A34A',
                    borderRadius: 4,
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 12 } }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#9CA3AF' },
                    border: { display: false }
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11 }, color: '#9CA3AF', stepSize: 5 },
                    border: { display: false }
                }
            }
        }
    });
</script>
@endpush

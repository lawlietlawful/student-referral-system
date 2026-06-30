@extends('layouts.admin')

@section('title', 'Behavioral Reports')
@section('page-title', 'Behavioral Reports')
@section('page-sub', 'Monitor student behavioral incidents reported by teachers')

@section('content')

{{-- ── Summary Cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white border border-gray-100 rounded-2xl shadow-premium p-6 flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-hover">
        <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center">
            <i class="ti ti-message-report text-gray-500 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-gray-900">{{ number_format($totalReports) }}</div>
        <div class="text-xs text-gray-400">Total Reports</div>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
            <i class="ti ti-clock text-blue-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-blue-700">{{ number_format($pendingCount) }}</div>
        <div class="text-xs text-gray-400">Pending Review</div>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
            <i class="ti ti-eye text-amber-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-amber-700">{{ number_format($reviewedCount) }}</div>
        <div class="text-xs text-gray-400">Reviewed</div>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
            <i class="ti ti-circle-check text-green-600 text-lg"></i>
        </div>
        <div class="text-2xl font-medium text-green-700">{{ number_format($resolvedCount) }}</div>
        <div class="text-xs text-gray-400">Resolved</div>
    </div>
</div>

{{-- ── Filters ───────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-xl p-4 mb-4">
    <form method="GET" action="{{ route('admin.behavioral-reports.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search Student</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Student ID..."
                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Severity</label>
            <select name="severity" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                <option value="">All</option>
                <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>Minor</option>
                <option value="moderate" {{ request('severity') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="severe" {{ request('severity') == 'severe' ? 'selected' : '' }}>Severe</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>
        @if($incidentTypes->count() > 0)
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Incident Type</label>
            <select name="incident_type" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                <option value="">All Types</option>
                @foreach($incidentTypes as $type)
                    <option value="{{ $type }}" {{ request('incident_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                <i class="ti ti-filter"></i> Filter
            </button>
            <a href="{{ route('admin.behavioral-reports.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5">
                <i class="ti ti-x"></i> Clear
            </a>
        </div>
    </form>
</div>

{{-- ── Reports Table ─────────────────────────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-2xl shadow-premium overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Student</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Incident Type</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Severity</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Status</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Reported By</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $report->student->last_name }}, {{ $report->student->first_name }}</p>
                            <p class="text-xs text-gray-500">{{ $report->student->student_id_number }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $report->incident_type }}</td>
                        <td class="px-5 py-3 text-center">
                            @php
                                $severityClass = match($report->severity) {
                                    'severe'   => 'bg-red-50 text-red-700',
                                    'moderate' => 'bg-amber-50 text-amber-700',
                                    'minor'    => 'bg-green-50 text-green-700',
                                    // Support teacher-submitted values too
                                    'Critical' => 'bg-red-50 text-red-700',
                                    'High'     => 'bg-red-50 text-red-700',
                                    'Medium'   => 'bg-amber-50 text-amber-700',
                                    'Low'      => 'bg-green-50 text-green-700',
                                    default    => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $severityClass }}">
                                {{ ucfirst($report->severity) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @php
                                $statusClass = match($report->status) {
                                    'pending'  => 'bg-blue-50 text-blue-700',
                                    'reviewed' => 'bg-amber-50 text-amber-700',
                                    'resolved' => 'bg-green-50 text-green-700',
                                    default    => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusClass }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $report->reportedBy->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $report->incident_date->format('M d, Y') }}</td>
                        <td class="px-5 py-3 text-center">
                            <a href="{{ route('admin.behavioral-reports.show', $report->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-3">
                                    <i class="ti ti-mood-check text-2xl"></i>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 mb-1">No Behavioral Reports</h3>
                                <p class="text-sm text-gray-500">Behavioral reports submitted by teachers will appear here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $reports->links() }}
        </div>
    @endif
</div>

@endsection

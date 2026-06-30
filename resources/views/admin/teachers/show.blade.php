@extends('layouts.admin')

@section('title', 'Teacher Profile')
@section('page-title', 'Teacher Profile')
@section('page-sub', 'View teacher activity and contribution details')

@section('content')

<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.teachers.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <i class="ti ti-arrow-left"></i> Back to Teachers
    </a>
    <a href="{{ route('admin.teachers.print', $teacher->id) }}" target="_blank" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm flex items-center gap-1.5">
        <i class="ti ti-printer text-blue-600"></i> Print / Export PDF
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Profile + Stats -->
    <div class="lg:col-span-1 space-y-6">

        <!-- Profile Card -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium p-6 text-center transition-all duration-300 hover:shadow-hover">
            <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-3xl mx-auto mb-4">
                {{ strtoupper(substr($teacher->name, 0, 1)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ $teacher->name }}</h3>
            <p class="text-sm text-gray-500 mt-0.5">{{ $teacher->email }}</p>
            @if($teacher->username)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-mono mt-2 mb-2">
                    @<span>{{ $teacher->username }}</span>
                </span>
            @endif
            
            <div class="mt-3">
                @if($teacher->engagement_status === 'highly_active')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        <i class="ti ti-bolt mr-1"></i> Highly Active
                    </span>
                @elseif($teacher->engagement_status === 'active')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                        <i class="ti ti-activity mr-1"></i> Active
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                        <i class="ti ti-moon mr-1"></i> Inactive
                    </span>
                @endif
            </div>

            <p class="text-xs text-gray-400 mt-4">Joined {{ $teacher->created_at->format('F j, Y') }}</p>
        </div>

        <!-- Activity Stats -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium p-6 transition-all duration-300 hover:shadow-hover">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="ti ti-chart-bar text-blue-600"></i> Activity Summary
            </h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <i class="ti ti-calendar-check text-green-500"></i> Attendance Records Filed
                    </span>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($totalAttendance) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <i class="ti ti-message-report text-amber-500"></i> Behavioral Reports Filed
                    </span>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($totalReports) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <i class="ti ti-circle-x text-red-500"></i> Total Absents Marked
                    </span>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($totalAbsentsMarked) }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Attendance Dates Heatmap -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium overflow-hidden transition-all duration-300 hover:shadow-hover">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2 text-sm">
                    <i class="ti ti-calendar text-blue-600"></i> 30-Day Activity Heatmap
                </h4>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-10 gap-2 justify-items-center max-w-fit mx-auto">
                    @foreach($attendanceDates as $date => $total)
                        @php
                            if ($total > 15) $color = 'bg-green-600';
                            elseif ($total > 5) $color = 'bg-green-500';
                            elseif ($total > 0) $color = 'bg-green-300';
                            else $color = 'bg-gray-100';
                        @endphp
                        <div class="w-4 h-4 rounded-sm {{ $color }} cursor-pointer hover:ring-2 hover:ring-offset-1 hover:ring-blue-400 transition" 
                             title="{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}: {{ $total }} records">
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center justify-end gap-2 text-xs text-gray-400">
                    <span>Less</span>
                    <div class="w-3 h-3 rounded-sm bg-gray-100"></div>
                    <div class="w-3 h-3 rounded-sm bg-green-300"></div>
                    <div class="w-3 h-3 rounded-sm bg-green-500"></div>
                    <div class="w-3 h-3 rounded-sm bg-green-600"></div>
                    <span>More</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Activity Tables -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Recent Attendance Records -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium overflow-hidden transition-all duration-300 hover:shadow-hover">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-calendar-check text-green-600"></i> Recent Attendance Records
                </h4>
            </div>
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 480px;">
                <table class="w-full text-left border-collapse relative">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Student</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($recentAttendance as $record)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-2.5">
                                    <p class="font-medium text-gray-900">{{ $record->student->last_name }}, {{ $record->student->first_name }}</p>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-gray-500">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-5 py-2.5 text-center">
                                    @php
                                        $statusBadge = match($record->status) {
                                            'present' => 'bg-green-50 text-green-700',
                                            'absent'  => 'bg-red-50 text-red-700',
                                            'late'    => 'bg-amber-50 text-amber-700',
                                            'excused' => 'bg-blue-50 text-blue-700',
                                            default   => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusBadge }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-gray-500">{{ $record->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-sm text-gray-400">No attendance records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Behavioral Reports -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium overflow-hidden transition-all duration-300 hover:shadow-hover">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-message-report text-amber-600"></i> Recent Behavioral Reports
                </h4>
            </div>
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 480px;">
                <table class="w-full text-left border-collapse relative">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Student</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Incident</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Severity</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($recentReports as $report)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-2.5">
                                    <p class="font-medium text-gray-900">{{ $report->student->last_name }}, {{ $report->student->first_name }}</p>
                                </td>
                                <td class="px-5 py-2.5 text-gray-600">{{ $report->incident_type }}</td>
                                <td class="px-5 py-2.5 text-center">
                                    @php
                                        $sevClass = match($report->severity) {
                                            'severe', 'Critical', 'High' => 'bg-red-50 text-red-700',
                                            'moderate', 'Medium'         => 'bg-amber-50 text-amber-700',
                                            default                      => 'bg-green-50 text-green-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $sevClass }}">
                                        {{ ucfirst($report->severity) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-center">
                                    @php
                                        $stClass = match($report->status) {
                                            'pending'  => 'bg-blue-50 text-blue-700',
                                            'reviewed' => 'bg-amber-50 text-amber-700',
                                            'resolved' => 'bg-green-50 text-green-700',
                                            default    => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $stClass }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-gray-500">{{ $report->incident_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-gray-400">No behavioral reports filed.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

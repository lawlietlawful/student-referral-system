@extends('layouts.admin')

@section('title', 'Attendance Monitoring')
@section('page-title', 'Attendance Monitoring')
@section('page-sub', 'View daily attendance records and identify students with excessive absences')

@section('content')

{{-- ── Date Selector & Stats ─────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    {{-- Present --}}
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center justify-between gap-3 hover:shadow-md transition">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <i class="ti ti-user-check text-green-600 text-lg"></i>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900 leading-none">{{ number_format($presentCount) }}</div>
                <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Present</div>
            </div>
        </div>
        <div class="flex flex-col items-end">
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $attendanceRate >= 90 ? 'bg-green-100 text-green-700' : ($attendanceRate >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                {{ $attendanceRate }}%
            </span>
            <span class="text-[9px] text-gray-400 mt-0.5 uppercase tracking-wider">Rate</span>
        </div>
    </div>

    {{-- Absent --}}
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
            <i class="ti ti-user-x text-red-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-gray-900 leading-none">{{ number_format($absentCount) }}</div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Absent</div>
        </div>
    </div>

    {{-- Late --}}
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
            <i class="ti ti-clock-exclamation text-amber-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-gray-900 leading-none">{{ number_format($lateCount) }}</div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Late</div>
        </div>
    </div>

    {{-- Excused --}}
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
            <i class="ti ti-clipboard-check text-blue-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-gray-900 leading-none">{{ number_format($excusedCount) }}</div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Excused</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Left: Attendance Records Table ────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Filters --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium p-4 mb-6">
            <form x-data method="GET" action="{{ route('admin.attendance.index') }}" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    
                    {{-- Date Range (Spans 2 cols) --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date Range</label>
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                @change="$el.closest('form').submit()"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring focus:ring-blue-100 transition shadow-sm py-2 cursor-pointer">
                            <span class="text-gray-400 text-sm">-</span>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                @change="$el.closest('form').submit()"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring focus:ring-blue-100 transition shadow-sm py-2 cursor-pointer">
                        </div>
                    </div>

                    {{-- Search (Spans 2/3 cols) --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search Student</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ti ti-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Student ID..."
                                @input.debounce.500ms="$el.closest('form').submit()"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition" autocomplete="off">
                        </div>
                    </div>
                    
                    {{-- Selects --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Course</label>
                        <select name="course" @change="$el.closest('form').submit()" class="block w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition cursor-pointer">
                            <option value="">All Courses</option>
                            @foreach($courses as $c)
                                <option value="{{ $c }}" {{ request('course') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Year Level</label>
                        <select name="grade_level" @change="$el.closest('form').submit()" class="block w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition cursor-pointer">
                            <option value="">All Years</option>
                            <option value="1st Year" {{ request('grade_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                            <option value="2nd Year" {{ request('grade_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                            <option value="3rd Year" {{ request('grade_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                            <option value="4th Year" {{ request('grade_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                            <option value="Irregular" {{ request('grade_level') == 'Irregular' ? 'selected' : '' }}>Irregular</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Section</label>
                        <select name="section" @change="$el.closest('form').submit()" class="block w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition cursor-pointer">
                            <option value="">All Sections</option>
                            @foreach($sections as $s)
                                <option value="{{ $s }}" {{ request('section') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" @change="$el.closest('form').submit()" class="block w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition cursor-pointer">
                            <option value="">All</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end justify-end gap-2 h-full mt-2 lg:mt-0">
                        <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center justify-center gap-1.5 shadow-sm whitespace-nowrap h-[38px] w-full lg:w-auto">
                            <i class="ti ti-refresh"></i> Reset
                        </a>
                        <a href="{{ route('admin.attendance.export', request()->query()) }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition shadow-sm flex items-center justify-center gap-1.5 h-[38px] whitespace-nowrap w-full lg:w-auto">
                            <i class="ti ti-download"></i> Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Records Table --}}
        {{-- Main Table Area --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-premium overflow-hidden" 
             x-data="{ 
                 selectedIds: [],
                 toggleAll(event) {
                     if (event.target.checked) {
                         this.selectedIds = Array.from(document.querySelectorAll('.attendance-checkbox')).map(cb => cb.value);
                     } else {
                         this.selectedIds = [];
                     }
                 }
             }">
             
            {{-- Bulk Actions Toolbar (Visible only when items selected) --}}
            <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50/80 border-b border-blue-100 px-5 py-3 flex items-center justify-between">
                <span class="text-sm font-medium text-blue-800">
                    <span x-text="selectedIds.length"></span> students selected
                </span>
                <form action="{{ route('admin.attendance.bulkSmsWarning') }}" method="POST" class="inline" @submit="if(!confirm('Send bulk SMS warnings to the selected students?')) event.preventDefault()">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="attendance_ids[]" :value="id">
                    </template>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                        <i class="ti ti-message-2"></i> Send Bulk SMS Warning
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3 w-10 text-center">
                                <input type="checkbox" @change="toggleAll" class="rounded border-gray-300 text-blue-600 focus:ring focus:ring-blue-200 transition cursor-pointer">
                            </th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Student</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Course / Section</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Recorded By</th>
                            <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" value="{{ $record->id }}" x-model="selectedIds" class="attendance-checkbox rounded border-gray-300 text-blue-600 focus:ring focus:ring-blue-200 transition cursor-pointer">
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ $record->student->last_name }}, {{ $record->student->first_name }}</p>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-600 border border-gray-200 shrink-0">{{ $record->student->student_id_number }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-700">{{ $record->student->course ?? 'N/A' }}</span>
                                        <span class="text-[11px] text-gray-500">{{ $record->student->grade_level }} — {{ $record->student->section }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @php
                                        $statusBadge = match($record->status) {
                                            'present' => 'bg-green-50 text-green-700',
                                            'absent'  => 'bg-red-50 text-red-700',
                                            'late'    => 'bg-amber-50 text-amber-700',
                                            'excused' => 'bg-blue-50 text-blue-700',
                                            default   => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusBadge }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-600 text-xs">{{ $record->teacher->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $record->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-24 h-24 mb-4 opacity-50">
                                            <svg class="w-full h-full text-blue-200" fill="currentColor" viewBox="0 0 24 24"><path d="M19,4H17V3a1,1,0,0,0-2,0V4H9V3A1,1,0,0,0,7,3V4H5A3,3,0,0,0,2,7V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V7A3,3,0,0,0,19,4Zm1,15a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V10H20ZM20,8H4V7A1,1,0,0,1,5,6H7V7A1,1,0,0,0,9,7V6h6V7a1,1,0,0,0,2,0V6h2a1,1,0,0,1,1,1Z"/></svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-1">No Attendance Found</h3>
                                        <p class="text-sm text-gray-500 max-w-sm mx-auto">It looks like there are no attendance records matching your criteria for the selected date range.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── Right: Top Absentees ──────────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden sticky top-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-alert-triangle text-red-500"></i> Top Absentees
                </h4>
                <p class="text-xs text-gray-400 mt-0.5">Students with the most absences overall</p>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($topAbsentees as $index => $student)
                    <div class="px-5 py-4 flex flex-col gap-2 hover:bg-gray-50/50 transition border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                                {{ $index < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-600 border border-gray-200 shrink-0">{{ $student->student_id_number }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 shrink-0">
                                        {{ $student->course ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200 shrink-0">
                                        {{ $student->grade_level }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200 shrink-0">
                                        {{ $student->section }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $student->absence_count >= 5 ? 'bg-red-100 text-red-700' : ($student->absence_count >= 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $student->absence_count }}
                                </span>
                                <p class="text-[9px] font-bold text-gray-400 mt-0.5 uppercase tracking-wider">absences</p>
                            </div>
                        </div>
                        
                        {{-- Quick Actions --}}
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 border-dashed">
                            <form action="{{ route('admin.attendance.smsWarning', $student->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" onclick="return confirm('Send automated SMS warning to parents regarding excessive absences?')" class="w-full flex items-center justify-center gap-1.5 py-1.5 rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-700 text-[11px] font-bold transition border border-gray-100 hover:border-blue-200">
                                    <i class="ti ti-message-2 text-sm"></i> SMS Parent
                                </button>
                            </form>
                            <a href="{{ route('admin.referrals.create', ['student_id' => $student->id, 'reason' => 'Excessive absences (' . $student->absence_count . '). Please intervene.']) }}" class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-700 text-[11px] font-bold transition border border-gray-100 hover:border-red-200">
                                <i class="ti ti-flag text-sm"></i> Issue Referral
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-gray-400">No absences recorded yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection

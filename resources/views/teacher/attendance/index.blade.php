@extends('layouts.teacher')

@section('title', 'Class Attendance')
@section('page-title', 'Class Attendance')
@section('page-sub', 'Mark student attendance for the selected date')

@section('content')

{{-- ── Summary Cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5"
     x-data="{ 
        stats: { total: {{ $students->count() }}, present: 0, late: 0, absent: 0 },
        updateStats() {
            let p = 0, l = 0, a = 0;
            document.querySelectorAll('input[type=\'radio\']:checked').forEach(el => {
                if(el.value === 'present') p++;
                if(el.value === 'late') l++;
                if(el.value === 'absent') a++;
            });
            this.stats.present = p;
            this.stats.late = l;
            this.stats.absent = a;
        },
        init() {
            this.$nextTick(() => this.updateStats());
        }
     }" 
     @attendance-changed.window="updateStats()">
     
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
            <i class="ti ti-users text-gray-500 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-gray-900 leading-none" x-text="stats.total"></div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Total Students</div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
            <i class="ti ti-check text-emerald-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-emerald-700 leading-none" x-text="stats.present"></div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Present</div>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
            <i class="ti ti-clock text-amber-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-amber-700 leading-none" x-text="stats.late"></div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Late</div>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
            <i class="ti ti-x text-red-600 text-lg"></i>
        </div>
        <div>
            <div class="text-lg font-bold text-red-700 leading-none" x-text="stats.absent"></div>
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mt-1">Absent</div>
        </div>
    </div>
</div>

<div x-data="{ 
    search: '',
    markAllPresent() {
        document.querySelectorAll('.attendance-radio-present').forEach(el => {
            el.checked = true;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });
        window.dispatchEvent(new Event('attendance-changed'));
    }
}">

    <div class="mb-6">
        <form action="{{ route('teacher.attendance.index') }}" method="GET" class="w-full" id="filterForm">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-premium p-4 flex flex-col lg:flex-row gap-3 items-end">
                <div class="flex-1 w-full relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search Student</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Search by name or ID..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition" autocomplete="off">
                    </div>
                </div>

                <div class="w-full lg:w-56">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                    <div class="flex items-center shadow-sm rounded-lg relative z-0">
                        <button type="button" onclick="changeDate(-1)" class="px-2.5 py-2 border border-gray-200 border-r-0 rounded-l-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition flex items-center justify-center relative z-10" title="Previous Day">
                            <i class="ti ti-chevron-left"></i>
                        </button>
                        <input type="date" name="date" id="dateInput" value="{{ $date }}" required onchange="document.getElementById('filterForm').submit();"
                            class="block w-full border border-gray-200 focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm transition py-2 px-2 text-center rounded-none z-20 relative">
                        <button type="button" onclick="changeDate(1)" class="px-2.5 py-2 border border-gray-200 border-l-0 rounded-r-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition flex items-center justify-center relative z-10" title="Next Day">
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="w-full lg:w-48">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Course / Program</label>
                    <select name="course" onchange="document.getElementById('filterForm').submit();" class="block w-full border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition py-2 px-3">
                        <option value="">All Courses</option>
                        @foreach($courses as $c)
                            <option value="{{ $c }}" {{ $course == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full lg:w-40">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Section</label>
                    <select name="section" onchange="document.getElementById('filterForm').submit();" class="block w-full border border-gray-200 rounded-lg focus:ring focus:ring-blue-100 focus:border-blue-500 text-sm shadow-sm transition py-2 px-3">
                        <option value="">All Sections</option>
                        @foreach($sections as $s)
                            <option value="{{ $s }}" {{ $section == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 w-full lg:w-auto mt-3 lg:mt-0">
                    <a href="{{ route('teacher.attendance.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center justify-center gap-1.5 w-full lg:w-auto whitespace-nowrap" title="Reset Filters">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                    <button type="submit" form="filterForm" formaction="{{ route('teacher.attendance.export') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition shadow-sm flex items-center justify-center gap-1.5 w-full lg:w-auto whitespace-nowrap">
                        <i class="ti ti-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden" @change="window.dispatchEvent(new Event('attendance-changed'))">
        @if($students->count() > 0)
            <form action="{{ route('teacher.attendance.bulk') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                
                <div class="p-4 border-b border-gray-100 flex justify-end bg-gray-50/50">
                    <button type="button" @click="markAllPresent()" class="w-full md:w-auto px-4 py-2 bg-white text-green-700 hover:bg-green-50 rounded-xl text-sm font-semibold transition border border-gray-200 shadow-sm flex items-center justify-center gap-2">
                        <i class="ti ti-checks"></i> Mark All Present
                    </button>
                </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">Course & Section</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 text-xs uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @foreach($students as $student)
                            @php
                                $att = $existingAttendance->get($student->id);
                                $status = $att ? $att->status : 'present'; // Default to present
                                $absenceType = $att ? ($att->absence_type ?? 'unexcused') : 'unexcused';
                                $remarks = $att ? $att->remarks : '';
                                $searchName = strtolower($student->first_name . ' ' . $student->last_name);
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition" 
                                x-show="search === '' || '{{ $searchName }}'.includes(search.toLowerCase())"
                                x-data="{ status: '{{ $status }}', absenceType: '{{ $absenceType }}' }">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                                                @if($student->total_absences > 0)
                                                    @php
                                                        $abs = $student->total_absences;
                                                        $badgeColor = $abs >= 3 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-amber-50 text-amber-600 border-amber-100';
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $badgeColor }}" title="Total Historical Absences">
                                                        <i class="ti ti-alert-circle text-[11px]"></i> {{ $abs }} {{ Str::plural('Absence', $abs) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $student->student_id_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-700">{{ $student->course }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->grade_level }} - {{ $student->section }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="inline-flex rounded-xl border border-gray-200 p-1 bg-gray-50 gap-1 shadow-inner w-full max-w-[180px]">
                                            <!-- Present -->
                                            <label class="cursor-pointer flex-1">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" class="hidden attendance-radio-present" x-model="status">
                                                <div class="px-2 py-1.5 text-[11px] font-bold rounded-lg transition-all duration-200 text-center"
                                                     :class="status === 'present' ? 'bg-white text-green-600 shadow border border-gray-200/50' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 border border-transparent'">
                                                    Present
                                                </div>
                                            </label>
                                            <!-- Late -->
                                            <label class="cursor-pointer flex-1">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="late" class="hidden" x-model="status">
                                                <div class="px-2 py-1.5 text-[11px] font-bold rounded-lg transition-all duration-200 text-center"
                                                     :class="status === 'late' ? 'bg-white text-amber-500 shadow border border-gray-200/50' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 border border-transparent'">
                                                    Late
                                                </div>
                                            </label>
                                            <!-- Absent -->
                                            <label class="cursor-pointer flex-1">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" class="hidden" x-model="status">
                                                <div class="px-2 py-1.5 text-[11px] font-bold rounded-lg transition-all duration-200 text-center"
                                                     :class="status === 'absent' ? 'bg-white text-red-500 shadow border border-gray-200/50' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 border border-transparent'">
                                                    Absent
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <!-- Absence Type Sub-menu -->
                                        <div x-show="status === 'absent'" x-collapse x-cloak class="w-full max-w-[180px]">
                                            <div class="inline-flex w-full rounded-lg border border-red-100 p-0.5 bg-red-50/50 gap-0.5 shadow-inner">
                                                <label class="cursor-pointer flex-1">
                                                    <input type="radio" name="attendance[{{ $student->id }}][absence_type]" value="unexcused" class="hidden" x-model="absenceType">
                                                    <div class="px-2 py-1 text-[10px] uppercase tracking-wider font-bold rounded-md transition-all duration-200 text-center"
                                                         :class="absenceType === 'unexcused' ? 'bg-red-500 text-white shadow' : 'text-red-400 hover:text-red-600 hover:bg-red-100/50'">
                                                        Unexcused
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer flex-1">
                                                    <input type="radio" name="attendance[{{ $student->id }}][absence_type]" value="excused" class="hidden" x-model="absenceType">
                                                    <div class="px-2 py-1 text-[10px] uppercase tracking-wider font-bold rounded-md transition-all duration-200 text-center"
                                                         :class="absenceType === 'excused' ? 'bg-white text-gray-700 shadow border border-gray-200/50' : 'text-red-400 hover:text-red-600 hover:bg-red-100/50'">
                                                        Excused
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" name="attendance[{{ $student->id }}][remarks]" value="{{ $remarks }}" placeholder="Optional notes..." 
                                        class="w-full text-sm rounded-xl border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 shadow-sm py-2 px-3 transition-colors bg-white hover:bg-gray-50">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-5 bg-white border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-500 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                        <i class="ti ti-info-circle"></i>
                    </span>
                    Marking a student Absent may trigger an automated SMS to their parent if they reach the absence threshold.
                </p>
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-all shadow-md shadow-gray-900/20 flex items-center justify-center gap-2">
                    <i class="ti ti-device-floppy"></i> Save Attendance
                </button>
            </div>
        </form>
    @else
        <div class="p-12 text-center text-gray-500 flex flex-col items-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100 shadow-inner">
                <i class="ti ti-users text-2xl text-gray-400"></i>
            </div>
            <p class="text-lg font-semibold text-gray-800">No students found</p>
            <p class="text-sm mt-1 text-gray-500 max-w-sm mx-auto">Try adjusting your date, course, or section filters above to find your students.</p>
        </div>
    @endif
</div>

<script>
function changeDate(days) {
    const dateInput = document.getElementById('dateInput');
    const date = new Date(dateInput.value);
    date.setDate(date.getDate() + days);
    dateInput.value = date.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}
</script>
@endsection

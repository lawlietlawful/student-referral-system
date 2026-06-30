@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-sub', 'Monitor student attendance and submit behavioral referrals')

@section('content')

{{-- ── Quick Stats ───────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white rounded-2xl p-6 shadow-premium border border-gray-100 flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-hover">
        <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl transition-transform group-hover:scale-110">
            <i class="ti ti-users"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Total Students</div>
            <div class="text-3xl font-bold text-gray-800">{{ $totalStudents }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-premium border border-gray-100 flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-hover">
        <div class="w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl transition-transform group-hover:scale-110">
            <i class="ti ti-file-export"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-400 uppercase tracking-wider">My Referrals</div>
            <div class="text-3xl font-bold text-gray-800">{{ $myReferrals }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-premium border border-gray-100 flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-hover">
        <div class="w-14 h-14 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-3xl transition-transform group-hover:scale-110">
            <i class="ti ti-clock"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Pending Review</div>
            <div class="text-3xl font-bold text-gray-800">{{ $myPendingReferrals }}</div>
        </div>
    </div>

</div>

{{-- ── Two Column Layout ────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Left: Threshold Alerts (Action Required) --}}
    <div class="lg:col-span-2 space-y-6">
        
        <div class="bg-white border border-red-100 rounded-2xl shadow-premium overflow-hidden">
            <div class="px-6 py-4 bg-red-50/50 border-b border-red-100 flex items-center justify-between">
                <h3 class="font-bold text-red-800 flex items-center gap-2">
                    <i class="ti ti-alert-triangle text-xl"></i> Action Required: High Absences
                </h3>
                <span class="text-xs font-semibold text-red-600 bg-red-100 px-2.5 py-1 rounded-full">Threshold: {{ $threshold }}</span>
            </div>
            
            <div class="p-0">
                @if($atRiskStudents->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($atRiskStudents as $student)
                            <div class="p-5 hover:bg-gray-50 transition flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                        <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                            <span>ID: {{ $student->student_id_number }}</span>
                                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            <span class="text-red-600 font-medium">{{ $student->total_absences }} Total Absences</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    @if($student->has_active_referral)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <i class="ti ti-check"></i> Referred
                                        </span>
                                    @else
                                        <a href="{{ route('teacher.referrals.create', ['student_id' => $student->id]) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-600 text-white hover:bg-red-700 transition shadow-sm">
                                            <i class="ti ti-file-export"></i> File Referral
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                            <i class="ti ti-shield-check"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-1">All clear!</h4>
                        <p class="text-sm text-gray-500">No students have reached the absence threshold yet.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right: Quick Actions --}}
    <div class="lg:col-span-1 space-y-6">
        
        <div class="bg-white rounded-2xl shadow-premium border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="ti ti-bolt text-amber-500"></i> Quick Actions
            </h3>
            
            <div class="space-y-3">
                <a href="{{ route('teacher.attendance.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/50 transition group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                        <i class="ti ti-calendar-plus text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Record Attendance</div>
                        <div class="text-xs text-gray-500">Mark daily attendance for class</div>
                    </div>
                </a>
                
                <a href="{{ route('teacher.referrals.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition group">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="ti ti-file-export text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">New Referral</div>
                        <div class="text-xs text-gray-500">Refer a student to guidance</div>
                    </div>
                </a>
                
                <a href="{{ route('teacher.behavioral-reports.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-amber-200 hover:bg-amber-50/50 transition group">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition">
                        <i class="ti ti-report text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Log Incident</div>
                        <div class="text-xs text-gray-500">File a behavioral report</div>
                    </div>
                </a>
            </div>
        </div>
        
    </div>
</div>

@endsection

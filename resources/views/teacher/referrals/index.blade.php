@extends('layouts.teacher')

@section('title', 'My Referrals')
@section('page-title', 'My Referrals')
@section('page-sub', 'Track the status of students you have referred to the guidance office')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex gap-4">
        <div class="bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm text-sm">
            <span class="text-gray-500">Total Filed:</span> 
            <span class="font-bold text-gray-900 ml-1">{{ $referrals->total() }}</span>
        </div>
        <div class="bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm text-sm">
            <span class="text-gray-500">Pending Review:</span> 
            <span class="font-bold text-orange-600 ml-1">{{ $pendingCount }}</span>
        </div>
    </div>
    
    <a href="{{ route('teacher.referrals.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
        <i class="ti ti-plus"></i> File New Referral
    </a>
</div>

<div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 font-medium">Date Filed</th>
                    <th class="px-6 py-4 font-medium">Student</th>
                    <th class="px-6 py-4 font-medium">Reason Type</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">AI Assessment & Seminars</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($referrals as $referral)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $referral->created_at->format('M d, Y') }}</span>
                            <div class="text-xs text-gray-500">{{ $referral->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $referral->student->first_name }} {{ $referral->student->last_name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $referral->student->student_id_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-800">{{ $referral->referral_type }}</span>
                            <div class="text-xs text-gray-500 line-clamp-1 max-w-xs truncate" title="{{ $referral->reason }}">{{ $referral->reason }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($referral->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-orange-50 text-orange-700 border border-orange-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Pending Review
                                </span>
                            @elseif($referral->status === 'in_progress')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="ti ti-loader text-blue-500"></i> In Progress
                                </span>
                            @elseif($referral->status === 'resolved')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                    <i class="ti ti-check text-green-500"></i> Resolved
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ ucfirst($referral->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            @if($referral->riskAssessment)
                                <div class="mb-1">
                                    @if($referral->riskAssessment->risk_level == 'high')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">High Risk ({{ $referral->riskAssessment->risk_score }}%)</span>
                                    @elseif($referral->riskAssessment->risk_level == 'moderate')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Moderate Risk ({{ $referral->riskAssessment->risk_score }}%)</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Low Risk ({{ $referral->riskAssessment->risk_score }}%)</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Not Assessed</span>
                            @endif
                            
                            @if($referral->student->seminars->count() > 0)
                                <div class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                                    <i class="ti ti-books"></i> {{ $referral->student->seminars->first()->title }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ti ti-file-export text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm">You haven't filed any referrals yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($referrals->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $referrals->links() }}
        </div>
    @endif
</div>

@endsection

@extends('layouts.teacher')

@section('title', 'Log Incident or Grade')
@section('page-title', 'Log Behavioral/Academic Report')
@section('page-sub', 'Record a failing grade or behavioral incident for a student')

@section('content')

<div class="bg-white border border-gray-100 rounded-2xl shadow-sm max-w-4xl">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
            <i class="ti ti-file-description text-amber-600"></i> New Report Form
        </h3>
        <a href="{{ route('teacher.behavioral-reports.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </div>

    <div class="p-6">
        <form action="{{ route('teacher.behavioral-reports.store') }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <!-- Student Selection -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Select Student <span class="text-red-500">*</span></label>
                    <select name="student_id" id="student_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('student_id') border-red-500 @enderror">
                        <option value="">Choose a student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->last_name }}, {{ $student->first_name }} ({{ $student->course }} - {{ $student->grade_level }} {{ $student->section }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Incident Type -->
                    <div>
                        <label for="incident_type" class="block text-sm font-medium text-gray-700 mb-1">Report Type <span class="text-red-500">*</span></label>
                        <select name="incident_type" id="incident_type" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('incident_type') border-red-500 @enderror">
                            <option value="">Select type...</option>
                            <option value="Academic Failure" {{ old('incident_type') === 'Academic Failure' ? 'selected' : '' }}>Academic Failure / Failing Grade</option>
                            <option value="Truancy" {{ old('incident_type') === 'Truancy' ? 'selected' : '' }}>Truancy / Cutting Classes</option>
                            <option value="Disciplinary Incident" {{ old('incident_type') === 'Disciplinary Incident' ? 'selected' : '' }}>Disciplinary Incident</option>
                            <option value="Other" {{ old('incident_type') === 'Other' ? 'selected' : '' }}>Other (Specify in description)</option>
                        </select>
                        @error('incident_type')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Severity -->
                    <div>
                        <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">Severity Level <span class="text-red-500">*</span></label>
                        <select name="severity" id="severity" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('severity') border-red-500 @enderror">
                            <option value="Low" {{ old('severity') === 'Low' ? 'selected' : '' }}>Low</option>
                            <option value="Medium" {{ old('severity') === 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="High" {{ old('severity') === 'High' ? 'selected' : '' }}>High (Triggers SMS to Parent)</option>
                            <option value="Critical" {{ old('severity') === 'Critical' ? 'selected' : '' }}>Critical (Triggers SMS to Parent)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">High and Critical severity levels will automatically notify the parent.</p>
                        @error('severity')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date -->
                    <div>
                        <label for="incident_date" class="block text-sm font-medium text-gray-700 mb-1">Date of Incident / Grade <span class="text-red-500">*</span></label>
                        <input type="date" name="incident_date" id="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('incident_date') border-red-500 @enderror">
                        @error('incident_date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location / Subject (Optional)</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" placeholder="e.g., Room 101 or IT102"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('location') border-red-500 @enderror">
                        @error('location')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" id="description" rows="4" required placeholder="Provide details of the failing grade or incident..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('teacher.behavioral-reports.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

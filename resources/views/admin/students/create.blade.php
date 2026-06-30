@extends('layouts.admin')

@section('title', 'Add New Student')
@section('page-title', 'Add New Student')
@section('page-sub', 'Register a student and automatically create their mobile portal account')

@section('content')

<div class="bg-white border border-gray-100 rounded-2xl shadow-premium max-w-5xl">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-lg">Student Information Form</h3>
        <a href="{{ route('admin.students.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Back to Students
        </a>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.students.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column: Student Details -->
                <div class="space-y-5">
                    <h4 class="font-medium text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i class="ti ti-user-edit text-blue-600"></i> Personal Details
                    </h4>
                    
                    <div>
                        <label for="student_id_number" class="block text-sm font-medium text-gray-700 mb-1">Student ID Number <span class="text-red-500">*</span></label>
                        <input type="text" name="student_id_number" id="student_id_number" value="{{ old('student_id_number') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('student_id_number') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">This will also be their login username.</p>
                        @error('student_id_number') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course / Program <span class="text-red-500">*</span></label>
                        <input type="text" name="course" id="course" value="{{ old('course') }}" required placeholder="e.g. BS Information Technology"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('course') border-red-500 @enderror">
                        @error('course') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('first_name') border-red-500 @enderror">
                            @error('first_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('last_name') border-red-500 @enderror">
                            @error('last_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('middle_name') border-red-500 @enderror">
                            @error('middle_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" id="gender" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('gender') border-red-500 @enderror">
                                <option value="">Select gender...</option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">Birthdate <span class="text-red-500">*</span></label>
                        <input type="date" name="birthdate" id="birthdate" value="{{ old('birthdate') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('birthdate') border-red-500 @enderror">
                        @error('birthdate') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Home Address <span class="text-red-500">*</span></label>
                        <textarea name="address" id="address" rows="2" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                        @error('address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Right Column: Academic & Parent Details -->
                <div class="space-y-5">
                    <h4 class="font-medium text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i class="ti ti-school text-amber-600"></i> Academic Details
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level <span class="text-red-500">*</span></label>
                            <select name="grade_level" id="grade_level" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                                <option value="">Select Year...</option>
                                <option value="1st Year" {{ old('grade_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                                <option value="2nd Year" {{ old('grade_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                <option value="3rd Year" {{ old('grade_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                <option value="4th Year" {{ old('grade_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                                <option value="5th Year" {{ old('grade_level') == '5th Year' ? 'selected' : '' }}>5th Year</option>
                            </select>
                            @error('grade_level') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section <span class="text-red-500">*</span></label>
                            <input type="text" name="section" id="section" value="{{ old('section') }}" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('section') border-red-500 @enderror">
                            @error('section') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year <span class="text-red-500">*</span></label>
                            <input type="text" name="school_year" id="school_year" value="{{ old('school_year', '2025-2026') }}" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('school_year') border-red-500 @enderror">
                            @error('school_year') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="transferred" {{ old('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                                <option value="graduated" {{ old('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <h4 class="font-medium text-gray-900 border-b border-gray-100 pb-2 mt-8 flex items-center gap-2">
                        <i class="ti ti-users text-green-600"></i> Parent / Guardian Contact
                    </h4>

                    <div>
                        <label for="parent_name" class="block text-sm font-medium text-gray-700 mb-1">Parent/Guardian Name <span class="text-red-500">*</span></label>
                        <input type="text" name="parent_name" id="parent_name" value="{{ old('parent_name') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('parent_name') border-red-500 @enderror">
                        @error('parent_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="parent_contact" class="block text-sm font-medium text-gray-700 mb-1">Parent Contact (SMS) <span class="text-red-500">*</span></label>
                            <input type="text" name="parent_contact" id="parent_contact" value="{{ old('parent_contact') }}" required
                                placeholder="e.g. 09123456789"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('parent_contact') border-red-500 @enderror">
                            @error('parent_contact') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="student_contact" class="block text-sm font-medium text-gray-700 mb-1">Student Contact (SMS)</label>
                            <input type="text" name="student_contact" id="student_contact" value="{{ old('student_contact') }}"
                                placeholder="e.g. 09123456789"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('student_contact') border-red-500 @enderror">
                            @error('student_contact') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="parent_email" class="block text-sm font-medium text-gray-700 mb-1">Parent Email (Optional)</label>
                        <input type="email" name="parent_email" id="parent_email" value="{{ old('parent_email') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('parent_email') border-red-500 @enderror">
                        @error('parent_email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-gray-500">The contact number will be used by the Semaphore API to send automated alerts regarding this student.</p>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i> Save Student
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

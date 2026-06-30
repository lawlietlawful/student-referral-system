@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-sub', 'Update details for ' . $user->name)

@section('content')

<div class="bg-white border border-gray-100 rounded-2xl shadow-premium max-w-3xl">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-lg">User Details</h3>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username (Optional for non-students) -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username (Optional)</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('username') border-red-500 @enderror">
                    @error('username')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">System Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required {{ $user->role === 'student' ? 'disabled' : '' }}
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('role') border-red-500 @enderror">
                        <option value="">Select a role...</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guidance_counselor" {{ old('role', $user->role) === 'guidance_counselor' || old('role', $user->role) === 'counselor' ? 'selected' : '' }}>Guidance Counselor</option>
                        <option value="teacher" {{ old('role', $user->role) === 'teacher' ? 'selected' : '' }}>Teacher</option>
                        @if($user->role === 'student')
                            <option value="student" selected>Student (Managed via Student Module)</option>
                        @endif
                    </select>
                    @if($user->role === 'student')
                        <input type="hidden" name="role" value="student">
                        <p class="mt-1 text-xs text-amber-600">Student roles cannot be changed here.</p>
                    @endif
                    @error('role')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Teacher Advisory Settings (Only show if role is teacher) -->
            <div id="teacher_advisory_section" class="mt-6 pt-6 border-t border-gray-100 {{ old('role', $user->role) === 'teacher' ? '' : 'hidden' }}">
                <h4 class="font-medium text-gray-800 mb-4">Teacher Advisory Settings</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="handled_course" class="block text-sm font-medium text-gray-700 mb-1">Handled Course</label>
                        <input type="text" name="handled_course" id="handled_course" value="{{ old('handled_course', $user->handled_course) }}" placeholder="e.g. BSIT"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                    </div>
                    <div>
                        <label for="handled_grade_level" class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
                        <input type="text" name="handled_grade_level" id="handled_grade_level" value="{{ old('handled_grade_level', $user->handled_grade_level) }}" placeholder="e.g. 1st Year"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                    </div>
                    <div>
                        <label for="handled_section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <input type="text" name="handled_section" id="handled_section" value="{{ old('handled_section', $user->handled_section) }}" placeholder="e.g. A"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Leaving these blank means the teacher will see all students.</p>
            </div>

            <div class="mt-6 space-y-6">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" id="password"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition shadow-sm @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Minimum 8 characters. Leave blank if you don't want to change it.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const advisorySection = document.getElementById('teacher_advisory_section');

        function toggleAdvisory() {
            if (roleSelect.value === 'teacher') {
                advisorySection.classList.remove('hidden');
            } else {
                advisorySection.classList.add('hidden');
                // Optional: clear values when hiding
                document.getElementById('handled_course').value = '';
                document.getElementById('handled_grade_level').value = '';
                document.getElementById('handled_section').value = '';
            }
        }

        if(roleSelect) {
            roleSelect.addEventListener('change', toggleAdvisory);
        }
    });
</script>
@endsection

@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-sub', 'Manage administrators, counselors, and teachers')

@section('content')

<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-premium">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="font-semibold text-gray-800 text-lg">System Users</h3>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
            <i class="ti ti-plus"></i> Add New User
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-blue-50/50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->role === 'admin')
                                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Admin</span>
                            @elseif($user->role === 'counselor')
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Counselor</span>
                            @elseif($user->role === 'teacher')
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-medium">Teacher</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">{{ ucfirst($user->role) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition" title="Edit">
                                    <i class="ti ti-edit text-lg"></i>
                                </a>
                                @if(auth()->id() !== $user->id)
                                    <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="$dispatch('open-confirm-modal', { 
                                                formId: 'delete-user-{{ $user->id }}', 
                                                title: 'Delete User', 
                                                message: 'Are you sure you want to delete {{ addslashes($user->name) }}? This action cannot be undone.',
                                                confirmText: 'Yes, Delete User'
                                            })" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete User">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <i class="ti ti-users text-4xl mb-3 block opacity-20"></i>
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection

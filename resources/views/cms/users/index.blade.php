@extends('layouts.cms')

@section('title', 'CMS User Management')

@section('content')
@include('cms.partials.flash')
<div class="overflow-hidden rounded-xl border border-green-100 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-green-100 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-green-950">CMS accounts</h2>
            <p class="mt-1 text-sm text-gray-600">Super Administrators and DPMO Website Administrators. Portal-wide user records are managed in the operational application.</p>
        </div>
            <a href="{{ route('users.create') }}" class="cms-btn cms-btn-primary">Create user</a>
    </div>
    <div class="cms-table-wrap overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-green-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->role }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('users.edit', $user) }}" class="text-green-800 underline">Edit in portal</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-600">No CMS accounts are listed.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $users->links() }}</div>
</div>
@endsection

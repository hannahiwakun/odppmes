@extends('layouts.admin')

@section('title', 'User Management')

@section('content')

@php
    $roleBadges = [
        'Super Administrator' => 'bg-red-100 text-red-800',
        'M&E Officer' => 'bg-blue-100 text-blue-800',
        'MDA Officer' => 'bg-green-100 text-green-800',
        'Development Partner' => 'bg-purple-100 text-purple-800',
        'Viewer' => 'bg-gray-100 text-gray-700',
    ];
@endphp

@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800" role="status">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">

    <div class="flex flex-col gap-4 border-b border-gray-200 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-900">User Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage system users and assign roles.</p>
        </div>

        <a href="{{ route('users.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Add User
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Email</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Role</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Ministry</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Organisation</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Registered</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->id }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $roleBadges[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->mda->name ?? 'None' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->developmentPartner->partner_name ?? 'None' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <a href="{{ route('users.show', $user->id) }}"
                                   class="rounded bg-green-700 px-3 py-1 text-sm text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    View
                                </a>

                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                    Edit
                                </a>

                                <form action="{{ route('users.destroy', $user->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4">
        {{ $users->links() }}
    </div>

</div>

@endsection

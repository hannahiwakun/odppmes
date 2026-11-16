@extends('layouts.admin')

@section('title', 'User Details')

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

<div class="mx-auto max-w-4xl space-y-6">

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-green-900">User Details</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $user->name }}</p>
            </div>

            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-green-700 bg-white px-5 py-2 font-semibold text-green-800 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Back
            </a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Account</h3>

        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-sm text-gray-500">Full Name</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $user->name }}</dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Email Address</dt>
                <dd class="break-all text-lg font-semibold text-gray-900">{{ $user->email }}</dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Role</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $roleBadges[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $user->role }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Assigned MDA</dt>
                <dd class="font-semibold text-gray-900">{{ $user->mda->name ?? 'None' }}</dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Development partner organisation</dt>
                <dd class="font-semibold text-gray-900">{{ $user->developmentPartner->partner_name ?? 'None' }}</dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Registered Date</dt>
                <dd class="font-semibold text-gray-900">{{ $user->created_at->format('d M Y') }}</dd>
            </div>
        </dl>
    </div>

</div>

@endsection

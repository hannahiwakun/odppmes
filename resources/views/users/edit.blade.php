@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Edit User</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Update this account, role, and ministry assignment.</p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800" role="alert">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Account</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="user-edit-name" class="mb-2 block font-semibold">
                        Full Name <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="user-edit-name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="user-edit-email" class="mb-2 block font-semibold">
                        Email Address <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="email"
                           id="user-edit-email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="user-edit-role" class="mb-2 block font-semibold">
                        User Role <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="user-edit-role"
                            name="role"
                            class="{{ $fieldClass }}"
                            required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) == $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="user-edit-mda" class="mb-2 block font-semibold">
                        Assigned MDA <span class="text-sm font-normal text-gray-500">Required for an MDA Officer</span>
                    </label>
                    <select id="user-edit-mda" name="mda_id" class="{{ $fieldClass }}">
                        <option value="">None. Required for an MDA Officer.</option>
                        @foreach($mdas as $mda)
                            <option value="{{ $mda->id }}" @selected((string) old('mda_id', $user->mda_id) === (string) $mda->id)>
                                {{ $mda->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-600">A ministry assignment is required for an MDA Officer.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="user-edit-partner" class="mb-2 block font-semibold">
                        Development partner organisation <span class="text-sm font-normal text-gray-500">Required for a Development Partner</span>
                    </label>
                    <select id="user-edit-partner" name="development_partner_id" class="{{ $fieldClass }}">
                        <option value="">None. Required for a Development Partner.</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" @selected((string) old('development_partner_id', $user->development_partner_id) === (string) $partner->id)>
                                {{ $partner->partner_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-600">A development partner organisation is required for a Development Partner.</p>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Password</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="user-edit-password" class="mb-2 block font-semibold">
                        New Password <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="password"
                           id="user-edit-password"
                           name="password"
                           class="{{ $fieldClass }}">
                    <p class="mt-2 text-sm text-gray-600">Leave blank if you do not want to change password.</p>
                </div>

                <div>
                    <label for="user-edit-password-confirmation" class="mb-2 block font-semibold">
                        Confirm New Password <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="password"
                           id="user-edit-password-confirmation"
                           name="password_confirmation"
                           class="{{ $fieldClass }}">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Update User
            </button>

            <a href="{{ route('users.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

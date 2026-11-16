@extends('layouts.admin')

@section('title', 'Add User')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Add New User</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Create an account and assign one of the portal roles.</p>

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

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Account</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="user-name" class="mb-2 block font-semibold">
                        Full Name <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="user-name"
                           name="name"
                           value="{{ old('name') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="user-email" class="mb-2 block font-semibold">
                        Email Address <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="email"
                           id="user-email"
                           name="email"
                           value="{{ old('email') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="user-role" class="mb-2 block font-semibold">
                        Role <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="user-role"
                            name="role"
                            class="{{ $fieldClass }}"
                            required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') == $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="user-mda" class="mb-2 block font-semibold">
                        Assigned MDA <span class="text-sm font-normal text-gray-500">Required for an MDA Officer</span>
                    </label>
                    <select id="user-mda" name="mda_id" class="{{ $fieldClass }}">
                        <option value="">None. Required for an MDA Officer.</option>
                        @foreach($mdas as $mda)
                            <option value="{{ $mda->id }}" @selected((string) old('mda_id') === (string) $mda->id)>
                                {{ $mda->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-600">A ministry assignment is required for an MDA Officer.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="user-partner" class="mb-2 block font-semibold">
                        Development partner organisation <span class="text-sm font-normal text-gray-500">Required for a Development Partner</span>
                    </label>
                    <select id="user-partner" name="development_partner_id" class="{{ $fieldClass }}">
                        <option value="">None. Required for a Development Partner.</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" @selected((string) old('development_partner_id') === (string) $partner->id)>
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
                    <label for="user-password" class="mb-2 block font-semibold">
                        Password <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="password"
                           id="user-password"
                           name="password"
                           class="{{ $fieldClass }}"
                           required>
                    <p class="mt-2 text-sm text-gray-600">The password must be at least eight characters and confirmed.</p>
                </div>

                <div>
                    <label for="user-password-confirmation" class="mb-2 block font-semibold">
                        Confirm Password <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="password"
                           id="user-password-confirmation"
                           name="password_confirmation"
                           class="{{ $fieldClass }}"
                           required>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Save User
            </button>

            <a href="{{ route('users.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

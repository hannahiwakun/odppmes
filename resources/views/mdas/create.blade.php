@extends('layouts.admin')

@section('title', 'Add New MDA')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $inputClass = fn (string $name) => $fieldClass.($errors->has($name) ? ' border-red-500' : '');
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Add New MDA</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Register a ministry, department, or agency for Ondo State.</p>

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

    <form action="{{ route('mdas.store') }}" method="POST">
        @csrf

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Alternate officer details are the only optional fields.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Ministry identity</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="mda-name" class="mb-2 block font-semibold">
                        MDA Name <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="mda-name"
                           name="name"
                           value="{{ old('name') }}"
                           class="{{ $inputClass('name') }}"
                           required
                           aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-code" class="mb-2 block font-semibold">
                        Code <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="mda-code"
                           name="code"
                           value="{{ old('code') }}"
                           class="{{ $inputClass('code') }}"
                           required
                           aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}">
                    @error('code')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-email" class="mb-2 block font-semibold">
                        Email <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="email"
                           id="mda-email"
                           name="email"
                           value="{{ old('email') }}"
                           class="{{ $inputClass('email') }}"
                           required
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-phone" class="mb-2 block font-semibold">
                        Phone <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="mda-phone"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="{{ $inputClass('phone') }}"
                           required
                           aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="mda-contact-person" class="mb-2 block font-semibold">
                        Contact Person <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="mda-contact-person"
                           name="contact_person"
                           value="{{ old('contact_person') }}"
                           class="{{ $inputClass('contact_person') }}"
                           required
                           aria-invalid="{{ $errors->has('contact_person') ? 'true' : 'false' }}">
                    @error('contact_person')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="mda-address" class="mb-2 block font-semibold">
                        Address <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <textarea id="mda-address"
                              name="address"
                              rows="3"
                              class="{{ $inputClass('address') }}"
                              required
                              aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="mda-status" class="mb-2 block font-semibold">
                        Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="mda-status"
                            name="status"
                            class="{{ $inputClass('status') }}"
                            required
                            aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}">
                        <option value="1" @selected((string) old('status', '1') === '1')>Active</option>
                        <option value="0" @selected((string) old('status', '1') === '0')>Inactive</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Focal person</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="mda-focal-name" class="mb-2 block font-semibold">
                        Development Partners Focal Person <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text" id="mda-focal-name" name="focal_name" value="{{ old('focal_name') }}" placeholder="Full name" class="{{ $inputClass('focal_name') }}" required aria-invalid="{{ $errors->has('focal_name') ? 'true' : 'false' }}">
                    @error('focal_name')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-focal-department" class="mb-2 block font-semibold">
                        Department <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text" id="mda-focal-department" name="focal_department" value="{{ old('focal_department') }}" class="{{ $inputClass('focal_department') }}" required aria-invalid="{{ $errors->has('focal_department') ? 'true' : 'false' }}">
                    @error('focal_department')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-focal-designation" class="mb-2 block font-semibold">
                        Focal Person Designation <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text" id="mda-focal-designation" name="focal_designation" value="{{ old('focal_designation') }}" class="{{ $inputClass('focal_designation') }}" required aria-invalid="{{ $errors->has('focal_designation') ? 'true' : 'false' }}">
                    @error('focal_designation')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="mda-focal-supervisor" class="mb-2 block font-semibold">
                        Supervising Director <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text" id="mda-focal-supervisor" name="focal_supervisor" value="{{ old('focal_supervisor') }}" class="{{ $inputClass('focal_supervisor') }}" required aria-invalid="{{ $errors->has('focal_supervisor') ? 'true' : 'false' }}">
                    @error('focal_supervisor')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-focal-phone" class="mb-2 block font-semibold">
                        Focal Person Phone <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text" id="mda-focal-phone" name="focal_phone" value="{{ old('focal_phone') }}" class="{{ $inputClass('focal_phone') }}" required aria-invalid="{{ $errors->has('focal_phone') ? 'true' : 'false' }}">
                    @error('focal_phone')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-focal-email" class="mb-2 block font-semibold">
                        Focal Person Email <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="email" id="mda-focal-email" name="focal_email" value="{{ old('focal_email') }}" class="{{ $inputClass('focal_email') }}" required aria-invalid="{{ $errors->has('focal_email') ? 'true' : 'false' }}">
                    @error('focal_email')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Alternate officer</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="mda-alternate-name" class="mb-2 block font-semibold">
                        Alternate Officer <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text" id="mda-alternate-name" name="alternate_name" value="{{ old('alternate_name') }}" class="{{ $inputClass('alternate_name') }}" aria-invalid="{{ $errors->has('alternate_name') ? 'true' : 'false' }}">
                    @error('alternate_name')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-alternate-phone" class="mb-2 block font-semibold">
                        Alternate Phone <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text" id="mda-alternate-phone" name="alternate_phone" value="{{ old('alternate_phone') }}" class="{{ $inputClass('alternate_phone') }}" aria-invalid="{{ $errors->has('alternate_phone') ? 'true' : 'false' }}">
                    @error('alternate_phone')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mda-alternate-email" class="mb-2 block font-semibold">
                        Alternate Email <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="email" id="mda-alternate-email" name="alternate_email" value="{{ old('alternate_email') }}" class="{{ $inputClass('alternate_email') }}" aria-invalid="{{ $errors->has('alternate_email') ? 'true' : 'false' }}">
                    @error('alternate_email')
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Save MDA
            </button>

            <a href="{{ route('mdas.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

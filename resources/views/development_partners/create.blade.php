@extends('layouts.admin')

@section('title', 'Add Development Partner')

@section('content')

@php
    $partnerType = (string) old('partner_type', '');
    $partnerStatus = (string) old('status', '1');
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Add Development Partner</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Register an organisation working with Ondo State on development projects.</p>

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

    <form action="{{ route('development-partners.store') }}" method="POST">
        @csrf

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Partner Identity</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="partner-name" class="mb-2 block font-semibold">
                        Partner Name <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="partner-name"
                           name="partner_name"
                           value="{{ old('partner_name') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="partner-acronym" class="mb-2 block font-semibold">
                        Acronym <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-acronym"
                           name="acronym"
                           value="{{ old('acronym') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-type" class="mb-2 block font-semibold">
                        Partner Type <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="partner-type"
                            name="partner_type"
                            class="{{ $fieldClass }}"
                            required>
                        <option value="" @selected($partnerType === '')>Select Type</option>
                        <option value="Government" @selected($partnerType === 'Government')>Government</option>
                        <option value="NGO" @selected($partnerType === 'NGO')>NGO</option>
                        <option value="UN Agency" @selected($partnerType === 'UN Agency')>UN Agency</option>
                        <option value="Bilateral" @selected($partnerType === 'Bilateral')>Bilateral</option>
                        <option value="Multilateral" @selected($partnerType === 'Multilateral')>Multilateral</option>
                        <option value="Private Sector" @selected($partnerType === 'Private Sector')>Private Sector</option>
                    </select>
                </div>

                <div>
                    <label for="partner-country" class="mb-2 block font-semibold">
                        Country <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-country"
                           name="country"
                           value="{{ old('country', 'Nigeria') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-status" class="mb-2 block font-semibold">
                        Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="partner-status"
                            name="status"
                            class="{{ $fieldClass }}"
                            required>
                        <option value="1" @selected($partnerStatus === '1')>Active</option>
                        <option value="0" @selected($partnerStatus === '0')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Contact Information</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="partner-contact-person" class="mb-2 block font-semibold">
                        Contact Person <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-contact-person"
                           name="contact_person"
                           value="{{ old('contact_person') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-email" class="mb-2 block font-semibold">
                        Email <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="email"
                           id="partner-email"
                           name="email"
                           value="{{ old('email') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-phone" class="mb-2 block font-semibold">
                        Phone <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-phone"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-website" class="mb-2 block font-semibold">
                        Website <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-website"
                           name="website"
                           value="{{ old('website') }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Address</h3>

            <label for="partner-address" class="mb-2 block font-semibold">
                Address <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="partner-address"
                      name="address"
                      rows="3"
                      class="{{ $fieldClass }}">{{ old('address') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Save Development Partner
            </button>

            <a href="{{ route('development-partners.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

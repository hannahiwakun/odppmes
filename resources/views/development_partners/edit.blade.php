@extends('layouts.admin')

@section('title', 'Edit Development Partner')

@section('content')

@php
    $partnerType = (string) old('partner_type', $development_partner->partner_type);
    $partnerStatus = (string) old('status', $development_partner->status ? '1' : '0');
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Edit Development Partner</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Update the record for this development partner.</p>

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

    <form action="{{ route('development-partners.update', $development_partner->id) }}" method="POST">
        @csrf
        @method('PUT')

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Partner Identity</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="partner-edit-name" class="mb-2 block font-semibold">
                        Partner Name <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="partner-edit-name"
                           name="partner_name"
                           value="{{ old('partner_name', $development_partner->partner_name) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="partner-edit-acronym" class="mb-2 block font-semibold">
                        Acronym <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-edit-acronym"
                           name="acronym"
                           value="{{ old('acronym', $development_partner->acronym) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-edit-type" class="mb-2 block font-semibold">
                        Partner Type <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="partner-edit-type"
                            name="partner_type"
                            class="{{ $fieldClass }}"
                            required>
                        <option value="Government" @selected($partnerType === 'Government')>Government</option>
                        <option value="NGO" @selected($partnerType === 'NGO')>NGO</option>
                        <option value="UN Agency" @selected($partnerType === 'UN Agency')>UN Agency</option>
                        <option value="Bilateral" @selected($partnerType === 'Bilateral')>Bilateral</option>
                        <option value="Multilateral" @selected($partnerType === 'Multilateral')>Multilateral</option>
                        <option value="Private Sector" @selected($partnerType === 'Private Sector')>Private Sector</option>
                    </select>
                </div>

                <div>
                    <label for="partner-edit-country" class="mb-2 block font-semibold">
                        Country <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-edit-country"
                           name="country"
                           value="{{ old('country', $development_partner->country) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-edit-status" class="mb-2 block font-semibold">
                        Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="partner-edit-status"
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
                    <label for="partner-edit-contact-person" class="mb-2 block font-semibold">
                        Contact Person <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-edit-contact-person"
                           name="contact_person"
                           value="{{ old('contact_person', $development_partner->contact_person) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-edit-email" class="mb-2 block font-semibold">
                        Email <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="email"
                           id="partner-edit-email"
                           name="email"
                           value="{{ old('email', $development_partner->email) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-edit-phone" class="mb-2 block font-semibold">
                        Phone <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-edit-phone"
                           name="phone"
                           value="{{ old('phone', $development_partner->phone) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="partner-edit-website" class="mb-2 block font-semibold">
                        Website <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="partner-edit-website"
                           name="website"
                           value="{{ old('website', $development_partner->website) }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Address</h3>

            <label for="partner-edit-address" class="mb-2 block font-semibold">
                Address <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="partner-edit-address"
                      name="address"
                      rows="3"
                      class="{{ $fieldClass }}">{{ old('address', $development_partner->address) }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Update Development Partner
            </button>

            <a href="{{ route('development-partners.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

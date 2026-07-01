@extends('layouts.admin')

@section('title','Edit Development Partner')

@section('content')

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Edit Development Partner
    </h2>

    @if ($errors->any())

        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-5">

            <ul class="list-disc pl-5">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('development-partners.update', $development_partner->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-2">Partner Name</label>

            <input
                type="text"
                name="partner_name"
                value="{{ old('partner_name', $development_partner->partner_name) }}"
                class="w-full border rounded px-4 py-2"
                required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Acronym</label>

            <input
                type="text"
                name="acronym"
                value="{{ old('acronym', $development_partner->acronym) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Partner Type</label>

            <select
                name="partner_type"
                class="w-full border rounded px-4 py-2"
                required>

                <option value="Government" {{ old('partner_type', $development_partner->partner_type) == 'Government' ? 'selected' : '' }}>Government</option>
                <option value="NGO" {{ old('partner_type', $development_partner->partner_type) == 'NGO' ? 'selected' : '' }}>NGO</option>
                <option value="UN Agency" {{ old('partner_type', $development_partner->partner_type) == 'UN Agency' ? 'selected' : '' }}>UN Agency</option>
                <option value="Bilateral" {{ old('partner_type', $development_partner->partner_type) == 'Bilateral' ? 'selected' : '' }}>Bilateral</option>
                <option value="Multilateral" {{ old('partner_type', $development_partner->partner_type) == 'Multilateral' ? 'selected' : '' }}>Multilateral</option>
                <option value="Private Sector" {{ old('partner_type', $development_partner->partner_type) == 'Private Sector' ? 'selected' : '' }}>Private Sector</option>

            </select>

        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Contact Person</label>

            <input
                type="text"
                name="contact_person"
                value="{{ old('contact_person', $development_partner->contact_person) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Email</label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $development_partner->email) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Phone</label>

            <input
                type="text"
                name="phone"
                value="{{ old('phone', $development_partner->phone) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Website</label>

            <input
                type="text"
                name="website"
                value="{{ old('website', $development_partner->website) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Country</label>

            <input
                type="text"
                name="country"
                value="{{ old('country', $development_partner->country) }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Address</label>

            <textarea
                name="address"
                rows="3"
                class="w-full border rounded px-4 py-2">{{ old('address', $development_partner->address) }}</textarea>
        </div>

        <div class="mb-5">

            <label class="block font-semibold mb-2">Status</label>

            <select
                name="status"
                class="w-full border rounded px-4 py-2">

                <option value="1" {{ old('status', $development_partner->status) == 1 ? 'selected' : '' }}>
                    Active
                </option>

                <option value="0" {{ old('status', $development_partner->status) == 0 ? 'selected' : '' }}>
                    Inactive
                </option>

            </select>

        </div>

       <div class="flex gap-3">

   <input
    type="submit"
    value="Update Development Partner"
    style="background:green;color:white;padding:12px 24px;border:none;border-radius:6px;font-weight:bold;">

    <a
        href="{{ route('development-partners.index') }}"
        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">

        Cancel

    </a>

</div>

    </form>

</div>

@endsection
```

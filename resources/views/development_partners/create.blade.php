@extends('layouts.admin')

@section('title','Add Development Partner')

@section('content')

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Add Development Partner
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

    <form action="{{ route('development-partners.store') }}" method="POST">

        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-2">Partner Name</label>

            <input
                type="text"
                name="partner_name"
                value="{{ old('partner_name') }}"
                class="w-full border rounded px-4 py-2"
                required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Acronym</label>

            <input
                type="text"
                name="acronym"
                value="{{ old('acronym') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Partner Type</label>

            <select
                name="partner_type"
                class="w-full border rounded px-4 py-2"
                required>

                <option value="">Select Type</option>
                <option value="Government">Government</option>
                <option value="NGO">NGO</option>
                <option value="UN Agency">UN Agency</option>
                <option value="Bilateral">Bilateral</option>
                <option value="Multilateral">Multilateral</option>
                <option value="Private Sector">Private Sector</option>

            </select>

        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Contact Person</label>

            <input
                type="text"
                name="contact_person"
                value="{{ old('contact_person') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Email</label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Phone</label>

            <input
                type="text"
                name="phone"
                value="{{ old('phone') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Website</label>

            <input
                type="text"
                name="website"
                value="{{ old('website') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Country</label>

            <input
                type="text"
                name="country"
                value="{{ old('country','Nigeria') }}"
                class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Address</label>

            <textarea
                name="address"
                rows="3"
                class="w-full border rounded px-4 py-2">{{ old('address') }}</textarea>
        </div>

        <div class="mb-5">

            <label class="block font-semibold mb-2">Status</label>

            <select
                name="status"
                class="w-full border rounded px-4 py-2">

                <option value="1">Active</option>
                <option value="0">Inactive</option>

            </select>

        </div>

        <div class="flex gap-3 border border-red-500 p-4">

    <button
    type="submit"
   class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-2 rounded-lg shadow">
     SAVE BUTTON
</button>

    <a href="{{ route('development-partners.index') }}"
       style="background:gray;color:white;padding:12px 20px;text-decoration:none;">
        Cancel
    </a>

</div>

    </form>

</div>

@endsection
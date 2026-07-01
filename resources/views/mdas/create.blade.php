@extends('layouts.admin')

@section('title', 'Add New MDA')

@section('content')

<div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Add New MDA
    </h2>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mdas.store') }}" method="POST">

        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-2">MDA Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border rounded px-4 py-2"
                   required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Code</label>
            <input type="text"
                   name="code"
                   value="{{ old('code') }}"
                   class="w-full border rounded px-4 py-2"
                   required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Phone</label>
            <input type="text"
                   name="phone"
                   value="{{ old('phone') }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Contact Person</label>
            <input type="text"
                   name="contact_person"
                   value="{{ old('contact_person') }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Address</label>
            <textarea name="address"
                      rows="3"
                      class="w-full border rounded px-4 py-2">{{ old('address') }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-2">Status</label>

            <select name="status"
                    class="w-full border rounded px-4 py-2">

                <option value="1">Active</option>
                <option value="0">Inactive</option>

            </select>

        </div>

        <div class="flex gap-3">

           <button type="submit"
        style="background:green;color:white;padding:10px 25px;border:none;border-radius:5px;">
    Save MDA
</button>

            <a href="{{ route('mdas.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection
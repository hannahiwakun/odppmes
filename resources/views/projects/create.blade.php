@extends('layouts.admin')

@section('title','Add Project')

@section('content')

<div class="max-w-6xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Add New Project
    </h2>

    @if ($errors->any())

        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-5">

            <ul class="list-disc pl-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('projects.store') }}" method="POST">

        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>

                <label class="block font-semibold mb-2">
                    Project Title
                </label>

                <input
                    type="text"
                    name="project_title"
                    value="{{ old('project_title') }}"
                    class="w-full border rounded px-4 py-2"
                    required>

            </div>

            <div>

                <label class="block font-semibold mb-2">
                    Project Code
                </label>

                <input
                    type="text"
                    name="project_code"
                    value="{{ old('project_code') }}"
                    class="w-full border rounded px-4 py-2">

            </div>

            <div>

                <label class="block font-semibold mb-2">
                    MDA
                </label>

                <select
                    name="mda_id"
                    class="w-full border rounded px-4 py-2"
                    required>

                    <option value="">Select MDA</option>

                    @foreach($mdas as $mda)

                        <option value="{{ $mda->id }}">

                            {{ $mda->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block font-semibold mb-2">
                    Development Partner
                </label>

                <select
                    name="development_partner_id"
                    class="w-full border rounded px-4 py-2">

                    <option value="">
                        None
                    </option>

                    @foreach($developmentPartners as $partner)

                        <option value="{{ $partner->id }}">

                            {{ $partner->partner_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block font-semibold mb-2">
                    Sector
                </label>

                <input
                    type="text"
                    name="sector"
                    class="w-full border rounded px-4 py-2">

            </div>

            <div>

                <label class="block font-semibold mb-2">
                    LGA
                </label>

                <input
                    type="text"
                    name="lga"
                    class="w-full border rounded px-4 py-2">

            </div>

        </div>

        <div class="mt-8 flex gap-3">

         <button
    type="submit"
    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded border border-blue-800">

    Update Monitoring

</button>

            <a
                href="{{ route('projects.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection
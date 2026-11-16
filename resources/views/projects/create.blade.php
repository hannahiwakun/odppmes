@extends('layouts.admin')

@section('title', 'Add Project')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedMda = (string) old('mda_id', '');
    $selectedPartner = (string) old('development_partner_id', '');
    $lockLeadMda = $lockLeadMda ?? false;
@endphp

<div class="mx-auto max-w-6xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Add New Project</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">
        @if($lockLeadMda)
            Register a development project for your assigned MDA. The lead MDA cannot be changed.
        @else
            Record a development project for Ondo State.
        @endif
    </p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800" role="alert">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Project Identity</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project-title" class="mb-2 block font-semibold">
                        Project Title <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-title"
                           name="project_title"
                           value="{{ old('project_title') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-code" class="mb-2 block font-semibold">
                        Project Code <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-code"
                           name="project_code"
                           value="{{ old('project_code') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-mda" class="mb-2 block font-semibold">
                        MDA <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    @if($lockLeadMda)
                        <p id="project-mda" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900">
                            {{ $assignedMda->name ?? 'Assigned MDA' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">Assigned automatically from your MDA Officer account.</p>
                    @else
                        <select id="project-mda" name="mda_id" class="{{ $fieldClass }}" required>
                            <option value="" @selected($selectedMda === '')>Select MDA</option>
                            @foreach($mdas as $mda)
                                <option value="{{ $mda->id }}" @selected($selectedMda === (string) $mda->id)>
                                    {{ $mda->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="project-partner" class="mb-2 block font-semibold">
                        Development Partner <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <select id="project-partner" name="development_partner_id" class="{{ $fieldClass }}">
                        <option value="" @selected($selectedPartner === '')>None</option>
                        @foreach($developmentPartners as $partner)
                            <option value="{{ $partner->id }}" @selected($selectedPartner === (string) $partner->id)>
                                {{ $partner->partner_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project-sector" class="mb-2 block font-semibold">
                        Sector <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-sector"
                           name="sector"
                           value="{{ old('sector') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-lga" class="mb-2 block font-semibold">
                        LGA <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-lga"
                           name="lga"
                           value="{{ old('lga') }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>
            </div>
        </div>

        @include('projects.partials.coordination-fields')

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Save Project
            </button>

            <a href="{{ route('projects.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

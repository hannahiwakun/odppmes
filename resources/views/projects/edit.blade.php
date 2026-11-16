@extends('layouts.admin')

@section('title', 'Edit Project')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedMda = (string) old('mda_id', $project->mda_id);
    $selectedPartner = (string) old('development_partner_id', $project->development_partner_id);
    $selectedRegisterStatus = (string) old('initiative_status', $project->initiative_status ?: $project->status);
    $lockLeadMda = $lockLeadMda ?? false;
@endphp

<div class="mx-auto max-w-6xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Edit Project</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Update this development project record.</p>

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

    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Project Identity</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project-edit-title" class="mb-2 block font-semibold">
                        Project Title <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-edit-title"
                           name="project_title"
                           value="{{ old('project_title', $project->project_title) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-edit-code" class="mb-2 block font-semibold">
                        Project Code <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-edit-code"
                           name="project_code"
                           value="{{ old('project_code', $project->project_code) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-edit-mda" class="mb-2 block font-semibold">
                        MDA <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    @if($lockLeadMda)
                        <p id="project-edit-mda" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900">
                            {{ $assignedMda->name ?? $project->mda->name ?? 'Assigned MDA' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">The lead MDA cannot be changed by an MDA Officer.</p>
                    @else
                        <select id="project-edit-mda" name="mda_id" class="{{ $fieldClass }}" required>
                            @foreach($mdas as $mda)
                                <option value="{{ $mda->id }}" @selected($selectedMda === (string) $mda->id)>
                                    {{ $mda->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="project-edit-partner" class="mb-2 block font-semibold">
                        Development Partner <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <select id="project-edit-partner" name="development_partner_id" class="{{ $fieldClass }}">
                        <option value="" @selected($selectedPartner === '')>None</option>
                        @foreach($developmentPartners as $partner)
                            <option value="{{ $partner->id }}" @selected($selectedPartner === (string) $partner->id)>
                                {{ $partner->partner_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project-edit-sector" class="mb-2 block font-semibold">
                        Sector <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-edit-sector"
                           name="sector"
                           value="{{ old('sector', $project->sector) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-edit-lga" class="mb-2 block font-semibold">
                        LGA <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="text"
                           id="project-edit-lga"
                           name="lga"
                           value="{{ old('lga', $project->lga) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-edit-ward" class="mb-2 block font-semibold">
                        Ward <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-edit-ward"
                           name="ward"
                           value="{{ old('ward', $project->ward) }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Progress and Time</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project-edit-register-status" class="mb-2 block font-semibold">
                        Register Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="project-edit-register-status" name="initiative_status" class="{{ $fieldClass }}" required>
                        @foreach(['Proposed','Under negotiation','Approved','Awaiting effectiveness','Planning','Ongoing','Delayed','Suspended','Completed','Cancelled'] as $registerStatus)
                            <option value="{{ $registerStatus }}" @selected($selectedRegisterStatus === $registerStatus)>
                                {{ $registerStatus }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-600">Saving a register status also updates the current project status. Delayed is recorded as Ongoing for the current status.</p>
                </div>

                <div>
                    <label for="project-edit-progress" class="mb-2 block font-semibold">
                        Progress (%) <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="number"
                           id="project-edit-progress"
                           name="progress"
                           min="0"
                           max="100"
                           value="{{ old('progress', $project->progress) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="project-edit-start-date" class="mb-2 block font-semibold">
                        Start Date <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="date"
                           id="project-edit-start-date"
                           name="start_date"
                           value="{{ old('start_date', $project->start_date ? \Illuminate\Support\Carbon::parse($project->start_date)->toDateString() : '') }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-edit-end-date" class="mb-2 block font-semibold">
                        End Date <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="date"
                           id="project-edit-end-date"
                           name="end_date"
                           value="{{ old('end_date', $project->end_date ? \Illuminate\Support\Carbon::parse($project->end_date)->toDateString() : '') }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Project Budget and Location</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project-edit-budget" class="mb-2 block font-semibold">
                        Budget <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="number"
                           id="project-edit-budget"
                           name="budget"
                           min="0"
                           step="0.01"
                           value="{{ old('budget', $project->budget) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-edit-funding-source" class="mb-2 block font-semibold">
                        Funding Source <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-edit-funding-source"
                           name="funding_source"
                           value="{{ old('funding_source', $project->funding_source) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-edit-latitude" class="mb-2 block font-semibold">
                        Latitude <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-edit-latitude"
                           name="latitude"
                           value="{{ old('latitude', $project->latitude) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div>
                    <label for="project-edit-longitude" class="mb-2 block font-semibold">
                        Longitude <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="project-edit-longitude"
                           name="longitude"
                           value="{{ old('longitude', $project->longitude) }}"
                           class="{{ $fieldClass }}">
                </div>

                <div class="md:col-span-2">
                    <label for="project-edit-description" class="mb-2 block font-semibold">
                        Description <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="project-edit-description"
                              name="description"
                              rows="4"
                              class="{{ $fieldClass }}">{{ old('description', $project->description) }}</textarea>
                </div>
            </div>
        </div>

        @include('projects.partials.coordination-fields')

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Update Project
            </button>

            <a href="{{ route('projects.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection

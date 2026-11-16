@extends('layouts.admin')

@section('title', 'Add Monitoring')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedStatus = (string) old('status', '');
@endphp

<div class="mx-auto max-w-5xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">New Project Monitoring</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Record a field visit for a development project.</p>

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

    <form action="{{ route('monitorings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Visit</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project_id" class="mb-2 block font-semibold">
                        Project <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="project_id" name="project_id" class="{{ $fieldClass }}" required>
                        <option value="" @selected((string) old('project_id', $selectedProject ?? '') === '')>Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) old('project_id', $selectedProject ?? '') === (string) $project->id)>
                                {{ $project->project_title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="monitoring_date" class="mb-2 block font-semibold">
                        Monitoring Date <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="date"
                           id="monitoring_date"
                           name="monitoring_date"
                           value="{{ old('monitoring_date', date('Y-m-d')) }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="status" class="mb-2 block font-semibold">
                        Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="status" name="status" class="{{ $fieldClass }}" required>
                        <option value="" @selected($selectedStatus === '')>Select Status</option>
                        @foreach(['Planning', 'Ongoing', 'Completed', 'Suspended', 'Cancelled'] as $visitStatus)
                            <option value="{{ $visitStatus }}" @selected($selectedStatus === $visitStatus)>{{ $visitStatus }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Progress</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="progress_before" class="mb-2 block font-semibold">
                        Progress Before (%) <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="number"
                           id="progress_before"
                           name="progress_before"
                           min="0"
                           max="100"
                           value="{{ old('progress_before', 0) }}"
                           class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="progress_after" class="mb-2 block font-semibold">
                        Progress After (%) <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="number"
                           id="progress_after"
                           name="progress_after"
                           min="0"
                           max="100"
                           value="{{ old('progress_after', 0) }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600">Saving this visit updates the project’s current status and progress, including when an older visit is edited. The development-register status is left unchanged.</p>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Field Notes</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="findings" class="mb-2 block font-semibold">
                        Findings <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="findings"
                              name="findings"
                              rows="4"
                              class="{{ $fieldClass }}"
                              placeholder="Enter findings from the monitoring visit...">{{ old('findings') }}</textarea>
                </div>
                <div>
                    <label for="challenges" class="mb-2 block font-semibold">
                        Challenges <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="challenges"
                              name="challenges"
                              rows="3"
                              class="{{ $fieldClass }}"
                              placeholder="Enter challenges or issues identified...">{{ old('challenges') }}</textarea>
                </div>
                <div>
                    <label for="recommendations" class="mb-2 block font-semibold">
                        Recommendations <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="recommendations"
                              name="recommendations"
                              rows="3"
                              class="{{ $fieldClass }}"
                              placeholder="Enter recommended actions...">{{ old('recommendations') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Photograph</h3>
            <label for="photo" class="mb-2 block font-semibold">
                Monitoring Photo <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="file"
                   id="photo"
                   name="photo"
                   accept="image/*"
                   class="{{ $fieldClass }}">
            <p class="mt-2 text-sm text-gray-500">Upload a photo taken during the monitoring visit. JPG and PNG files up to 4MB are accepted. Additional photographs and MP4 video can be added from the visit details page after this record is saved.</p>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Location</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="latitude" class="mb-2 block font-semibold">
                        Latitude <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="latitude"
                           name="latitude"
                           value="{{ old('latitude') }}"
                           placeholder="Latitude"
                           class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="longitude" class="mb-2 block font-semibold">
                        Longitude <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="longitude"
                           name="longitude"
                           value="{{ old('longitude') }}"
                           placeholder="Longitude"
                           class="{{ $fieldClass }}">
                </div>
            </div>
            <button type="button"
                    onclick="getLocation()"
                    class="mt-4 rounded-lg bg-green-700 px-6 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Get Current Location
            </button>
            <p id="location-status" class="mt-2 text-sm text-gray-500">
                Click the button to capture your current GPS coordinates.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Save Monitoring
            </button>
            <a href="{{ route('monitorings.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

<script>
function getLocation() {
    const status = document.getElementById('location-status');

    if (!navigator.geolocation) {
        status.textContent = 'Geolocation is not supported by your browser.';
        return;
    }

    status.textContent = 'Getting your current location...';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(7);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(7);
            status.textContent = 'GPS location captured successfully.';
            status.classList.remove('text-gray-500');
            status.classList.add('text-green-600');
        },
        function() {
            status.textContent = 'Unable to retrieve your GPS location. Please allow location access or enter the coordinates manually.';
            status.classList.remove('text-gray-500');
            status.classList.add('text-red-600');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}
</script>

@endsection

@extends('layouts.admin')

@section('title', 'Edit Monitoring')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedStatus = (string) old('status', $monitoring->status);
    $monitoringDate = old('monitoring_date', $monitoring->monitoring_date ? \Illuminate\Support\Carbon::parse($monitoring->monitoring_date)->toDateString() : '');
@endphp

<div class="mx-auto max-w-5xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Edit Project Monitoring</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">Update this field visit.</p>

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

    <form action="{{ route('monitorings.update', $monitoring->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <p class="mb-6 text-sm text-gray-600">Fields marked with <span class="text-red-700">*</span> are required. Other fields are optional.</p>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Visit</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="monitoring-edit-project" class="mb-2 block font-semibold">
                        Project <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="monitoring-edit-project" name="project_id" class="{{ $fieldClass }}" required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) old('project_id', $monitoring->project_id) === (string) $project->id)>
                                {{ $project->project_title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="monitoring-edit-date" class="mb-2 block font-semibold">
                        Monitoring Date <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <input type="date"
                           id="monitoring-edit-date"
                           name="monitoring_date"
                           value="{{ $monitoringDate }}"
                           class="{{ $fieldClass }}"
                           required>
                </div>

                <div>
                    <label for="monitoring-edit-status" class="mb-2 block font-semibold">
                        Status <span class="text-red-700" aria-hidden="true">*</span>
                        <span class="sr-only">required</span>
                    </label>
                    <select id="monitoring-edit-status" name="status" class="{{ $fieldClass }}" required>
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
                    <label for="monitoring-edit-progress-before" class="mb-2 block font-semibold">
                        Progress Before (%) <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="number"
                           id="monitoring-edit-progress-before"
                           name="progress_before"
                           min="0"
                           max="100"
                           value="{{ old('progress_before', $monitoring->progress_before) }}"
                           class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="monitoring-edit-progress-after" class="mb-2 block font-semibold">
                        Progress After (%) <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="number"
                           id="monitoring-edit-progress-after"
                           name="progress_after"
                           min="0"
                           max="100"
                           value="{{ old('progress_after', $monitoring->progress_after) }}"
                           class="{{ $fieldClass }}">
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600">Saving this visit updates the project’s current status and progress, including when an older visit is edited. The development-register status is left unchanged.</p>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Field Notes</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="monitoring-edit-findings" class="mb-2 block font-semibold">
                        Findings <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="monitoring-edit-findings"
                              name="findings"
                              rows="4"
                              class="{{ $fieldClass }}">{{ old('findings', $monitoring->findings) }}</textarea>
                </div>
                <div>
                    <label for="monitoring-edit-challenges" class="mb-2 block font-semibold">
                        Challenges <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="monitoring-edit-challenges"
                              name="challenges"
                              rows="3"
                              class="{{ $fieldClass }}">{{ old('challenges', $monitoring->challenges) }}</textarea>
                </div>
                <div>
                    <label for="monitoring-edit-recommendations" class="mb-2 block font-semibold">
                        Recommendations <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <textarea id="monitoring-edit-recommendations"
                              name="recommendations"
                              rows="3"
                              class="{{ $fieldClass }}">{{ old('recommendations', $monitoring->recommendations) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Photograph</h3>
            @if($monitoring->photo)
                <div class="mb-4">
                    <p class="mb-2 font-semibold">Current Monitoring Photo</p>
                    <img src="{{ route('monitorings.photo', $monitoring) }}"
                         alt="Monitoring photo"
                         class="w-64 rounded-lg border border-gray-200">
                </div>
            @endif
            <label for="monitoring-edit-photo" class="mb-2 block font-semibold">
                Replace Monitoring Photo <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="file"
                   id="monitoring-edit-photo"
                   name="photo"
                   accept="image/*"
                   class="{{ $fieldClass }}">
            <p class="mt-2 text-sm text-gray-500">Leave blank if you don't want to replace the current photo. JPG and PNG files up to 4MB are accepted. Additional photographs and MP4 video are managed from the visit details page and are not replaced by this field.</p>
            <p class="mt-2 text-sm">
                <a href="{{ route('monitorings.show', $monitoring) }}" class="font-semibold text-green-800 hover:underline">Open visit details to add monitoring evidence</a>
            </p>
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
                           value="{{ old('latitude', $monitoring->latitude) }}"
                           readonly
                           class="{{ $fieldClass }} bg-gray-100">
                </div>
                <div>
                    <label for="longitude" class="mb-2 block font-semibold">
                        Longitude <span class="text-sm font-normal text-gray-500">Optional</span>
                    </label>
                    <input type="text"
                           id="longitude"
                           name="longitude"
                           value="{{ old('longitude', $monitoring->longitude) }}"
                           readonly
                           class="{{ $fieldClass }} bg-gray-100">
                </div>
            </div>
            <button type="button"
                    onclick="getLocation()"
                    class="mt-4 rounded-lg bg-green-700 px-6 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Capture New GPS Location
            </button>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Update Monitoring
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
    if (!navigator.geolocation) {
        alert("Geolocation is not supported.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        },
        function() {
            alert("Unable to retrieve your location.");
        }
    );
}
</script>

@endsection

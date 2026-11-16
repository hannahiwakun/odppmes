@extends('layouts.admin')

@section('title', 'Project Details')

@section('content')

@php
    $statusBadge = match ($project->status) {
        'Planning' => 'bg-gray-100 text-gray-700',
        'Ongoing' => 'bg-green-100 text-green-800',
        'Completed' => 'bg-green-200 text-green-900',
        'Suspended' => 'bg-amber-100 text-amber-900',
        'Cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
    $progressWidth = min(max((int) ($project->progress ?? 0), 0), 100);
    $startDate = $project->start_date ? \Illuminate\Support\Carbon::parse($project->start_date)->format('d M Y') : null;
    $endDate = $project->end_date ? \Illuminate\Support\Carbon::parse($project->end_date)->format('d M Y') : null;
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-green-900">{{ $project->project_title }}</h2>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                        {{ $project->status ?? 'Not Set' }}
                    </span>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Project code:
                    <span class="font-semibold text-gray-900">{{ $project->project_code ?: '-' }}</span>
                </p>
                @if(filled($project->initiative_status))
                    <p class="mt-1 text-sm text-gray-600">Register status: {{ $project->initiative_status }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('projects.index') }}"
                   class="rounded-lg bg-gray-500 px-5 py-2 font-semibold text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Back to Projects
                </a>
                @if(Route::has('projects.edit'))
                    <a href="{{ route('projects.edit', $project) }}"
                       class="rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                        Edit Project
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Project budget</p>
            <p class="mt-3 text-2xl font-bold text-green-900">₦{{ number_format($project->budget ?? 0, 2) }}</p>
            <p class="mt-2 text-sm text-gray-600">This is the project budget field.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Current status</p>
            <div class="mt-3">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                    {{ $project->status ?? 'Not Set' }}
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Current progress</p>
                <span class="text-2xl font-bold text-green-900">{{ $project->progress ?? 0 }}%</span>
            </div>
            <div class="mt-5 h-2 overflow-hidden rounded-full bg-gray-200"
                 role="progressbar"
                 aria-valuenow="{{ $progressWidth }}"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-label="Current progress">
                <div class="h-2 rounded-full bg-green-700" style="width: {{ $progressWidth }}%"></div>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h2 class="mb-6 text-xl font-bold text-green-900">Project Information</h2>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Implementing MDA</p>
                <p class="mt-2 font-bold">{{ $project->mda->name ?? '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Development Partner</p>
                <p class="mt-2 font-bold">{{ $project->developmentPartner->partner_name ?? '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Sector</p>
                <p class="mt-2 font-bold">{{ $project->sector ? ucwords($project->sector) : '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">LGA</p>
                <p class="mt-2 font-bold">{{ $project->lga ? ucwords($project->lga) : '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Ward</p>
                <p class="mt-2 font-bold">{{ $project->ward ?: '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Funding Source</p>
                <p class="mt-2 font-bold">{{ $project->funding_source ?: '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Start Date</p>
                <p class="mt-2 font-bold">{{ $startDate ?: '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">End Date</p>
                <p class="mt-2 font-bold">{{ $endDate ?: '-' }}</p>
            </div>
        </div>
    </div>

    @include('projects.partials.coordination-summary')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h2 class="mb-4 font-bold text-green-900">Project Description</h2>
        <p class="whitespace-pre-line leading-7 text-gray-700">
            {{ $project->description ?: 'No description available.' }}
        </p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-bold text-green-900">Project Location</h2>

            @if($project->latitude && $project->longitude)
                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">GPS Available</span>
            @else
                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">GPS Not Added</span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Latitude</p>
                <p class="mt-2 font-bold">{{ $project->latitude ?? 'Not Available' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Longitude</p>
                <p class="mt-2 font-bold">{{ $project->longitude ?? 'Not Available' }}</p>
            </div>
        </div>

        @if($project->latitude && $project->longitude)
            <div class="mt-6">
                <div id="projectMap" style="height: 400px;" class="rounded-lg shadow"></div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-green-900">Project Documents</h2>
            <p class="mt-1 text-sm text-gray-500">Proposals, reports, agreements and other project files.</p>
            <a href="{{ route('project-documents.index', $project) }}"
               class="mt-5 inline-block rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                View Documents
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-green-900">Project Gallery</h2>
            <p class="mt-1 text-sm text-gray-500">View photographs captured during project monitoring.</p>
            <a href="{{ route('projects.photos.index', $project) }}"
               class="mt-5 inline-block rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                View Gallery
            </a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-green-900">Monitoring History</h2>
                <p class="mt-1 text-sm text-gray-500">Field monitoring records and project implementation updates.</p>
            </div>
            <a href="{{ route('monitorings.create', ['project' => $project->id]) }}"
               class="rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Add Monitoring
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Date</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Officer</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Status</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Progress</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($project->monitorings as $monitoring)
                        @php
                            $monitoringStatusClass = match ($monitoring->status) {
                                'Planning' => 'bg-gray-100 text-gray-700',
                                'Ongoing' => 'bg-green-100 text-green-800',
                                'Completed' => 'bg-green-200 text-green-900',
                                'Suspended' => 'bg-amber-100 text-amber-900',
                                'Cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                {{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}
                            </td>
                            <td class="px-3 py-3 text-sm">{{ $monitoring->user->name ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $monitoringStatusClass }}">
                                    {{ $monitoring->status }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm font-semibold text-green-800">
                                {{ $monitoring->progress_after ?? 0 }}%
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if(Route::has('monitorings.show'))
                                    <a href="{{ route('monitorings.show', $monitoring) }}"
                                       class="inline-block rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                                <p class="font-semibold">No monitoring records found.</p>
                                <p class="mt-1 text-sm">Use Add Monitoring to record the first field monitoring visit.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if($project->latitude && $project->longitude)

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var latitude = {{ $project->latitude }};
        var longitude = {{ $project->longitude }};

        var map = L.map('projectMap').setView([latitude, longitude], 15);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([latitude, longitude])
            .addTo(map)
            .bindPopup(@json($project->project_title))
            .openPopup();
    });
</script>

@endif

@endsection

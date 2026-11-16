@extends('layouts.admin')

@section('title', 'Monitoring Details')

@section('content')

@php
    $statusBadge = match ($monitoring->status) {
        'Planning' => 'bg-gray-100 text-gray-700',
        'Ongoing' => 'bg-green-100 text-green-800',
        'Completed' => 'bg-green-200 text-green-900',
        'Suspended' => 'bg-amber-100 text-amber-900',
        'Cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
    $progressBefore = (int) ($monitoring->progress_before ?? 0);
    $progressAfter = (int) ($monitoring->progress_after ?? 0);
    $progressChange = $progressAfter - $progressBefore;
    $progressWidth = min(max($progressAfter, 0), 100);
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    @if(session('success'))
        <div class="rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800 print:hidden" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-800 print:hidden" role="alert">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hidden print:block">
        <h1 class="text-2xl font-bold">PROJECT MONITORING REPORT</h1>
        <p class="mt-2">{{ $monitoring->project->project_title ?? 'Project' }}</p>
        <p class="text-sm">
            Monitoring Date:
            {{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}
        </p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow print:hidden">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-green-900">{{ $monitoring->project->project_title ?? 'N/A' }}</h2>
                <p class="mt-1 text-sm text-gray-600">Project Monitoring Report</p>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                        {{ $monitoring->status ?? 'Not Set' }}
                    </span>
                    <span class="text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}
                    </span>
                    @if($monitoring->project->project_code ?? false)
                        <span class="text-sm text-gray-600">Code: {{ $monitoring->project->project_code }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('monitorings.edit', $monitoring->id) }}"
                   class="rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Edit
                </a>
                <button type="button"
                        onclick="window.print()"
                        class="rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Print
                </button>
                <a href="{{ route('projects.show', $monitoring->project) }}"
                   class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Back to Project
                </a>
                <a href="{{ route('monitorings.index') }}"
                   class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    All Monitorings
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-5 text-xl font-bold text-green-900">Monitoring Information</h3>
            <dl class="space-y-4">
                <div class="flex justify-between gap-4 border-b border-gray-200 pb-3">
                    <dt class="text-gray-500">Project</dt>
                    <dd class="text-right font-semibold">{{ $monitoring->project->project_title ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-200 pb-3">
                    <dt class="text-gray-500">Monitoring Officer</dt>
                    <dd class="text-right font-semibold">{{ $monitoring->user->name ?? 'Unknown Officer' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-200 pb-3">
                    <dt class="text-gray-500">Monitoring Date</dt>
                    <dd class="font-semibold">{{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                            {{ $monitoring->status ?? 'Not Set' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-5 text-xl font-bold text-green-900">Project Progress</h3>
            <div class="mb-5 grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-500">Before Monitoring</p>
                    <p class="mt-2 text-3xl font-bold text-gray-700">{{ $monitoring->progress_before ?? 0 }}%</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-green-50 p-4">
                    <p class="text-sm text-green-800">After Monitoring</p>
                    <p class="mt-2 text-3xl font-bold text-green-800">{{ $monitoring->progress_after ?? 0 }}%</p>
                </div>
            </div>
            <div class="mb-2 flex justify-between">
                <span class="text-sm text-gray-500">Current Progress</span>
                <span class="font-bold text-green-800">{{ $progressAfter }}%</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-gray-200"
                 role="progressbar"
                 aria-valuenow="{{ $progressWidth }}"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-label="Current progress">
                <div class="h-3 rounded-full bg-green-700" style="width: {{ $progressWidth }}%"></div>
            </div>
            <div class="mt-4">
                @if($progressChange > 0)
                    <span class="font-semibold text-green-800">
                        ↑ {{ $progressChange }} percentage point{{ $progressChange == 1 ? '' : 's' }} improvement
                    </span>
                @elseif($progressChange < 0)
                    <span class="font-semibold text-red-700">
                        ↓ {{ abs($progressChange) }} percentage point{{ abs($progressChange) == 1 ? '' : 's' }} decrease
                    </span>
                @else
                    <span class="font-semibold text-gray-600">No change in recorded progress</span>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-3 text-xl font-bold text-green-900">Findings</h3>
        <div class="whitespace-pre-line rounded-lg bg-gray-50 p-5 leading-7 text-gray-700">
            {{ $monitoring->findings ?: 'No findings were recorded for this monitoring visit.' }}
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-3 text-xl font-bold text-green-900">Challenges</h3>
        <div class="whitespace-pre-line rounded-lg bg-gray-50 p-5 leading-7 text-gray-700">
            {{ $monitoring->challenges ?: 'No challenges were recorded for this monitoring visit.' }}
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-3 text-xl font-bold text-green-900">Recommendations</h3>
        <div class="whitespace-pre-line rounded-lg bg-gray-50 p-5 leading-7 text-gray-700">
            {{ $monitoring->recommendations ?: 'No recommendations were recorded for this monitoring visit.' }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-4 text-xl font-bold text-green-900">Monitoring Photo</h3>
            @if($monitoring->photo)
                <div class="flex justify-center">
                    <img src="{{ route('monitorings.photo', $monitoring) }}"
                         alt="Monitoring photo"
                         class="max-h-[500px] w-auto rounded-lg object-contain">
                </div>
            @else
                <div class="rounded-lg bg-gray-100 p-12 text-center text-gray-500">
                    <p>No photo was uploaded for this monitoring visit.</p>
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-4 text-xl font-bold text-green-900">Monitoring Location</h3>
            @if($monitoring->latitude && $monitoring->longitude)
                <div class="mb-5 grid grid-cols-2 gap-4">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Latitude</p>
                        <p class="mt-1 font-bold">{{ $monitoring->latitude }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Longitude</p>
                        <p class="mt-1 font-bold">{{ $monitoring->longitude }}</p>
                    </div>
                </div>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <iframe
                        width="100%"
                        height="320"
                        frameborder="0"
                        style="border:0"
                        src="https://maps.google.com/maps?q={{ $monitoring->latitude }},{{ $monitoring->longitude }}&z=15&output=embed"
                        allowfullscreen
                        loading="lazy"
                        title="Monitoring location"
                    ></iframe>
                </div>
            @else
                <div class="rounded-lg bg-amber-50 p-8 text-center text-amber-900">
                    <p>GPS coordinates were not recorded for this monitoring visit.</p>
                </div>
            @endif
        </div>
    </div>

    @include('monitorings.partials.evidence')

</div>

<style>
@media print {
    body {
        background: white !important;
    }

    .shadow-lg,
    .shadow {
        box-shadow: none !important;
    }

    a {
        text-decoration: none !important;
    }

    @page {
        margin: 15mm;
    }
}
</style>

@endsection

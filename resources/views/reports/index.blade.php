@extends('layouts.admin')

@section('title', $reportsHeading)

@section('content')

@php
    $canOpenProject = auth()->user()->isSuperAdmin() || auth()->user()->isMEO() || auth()->user()->isMdaOfficer();
@endphp

<div class="space-y-6">

    <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-6 shadow sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-green-900">{{ $reportsHeading }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $reportsIntro }}</p>
        </div>

        <a href="{{ route('reports.pdf', request()->query()) }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Export PDF
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Projects Found</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $summary['total'] }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Average Progress</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $summary['average'] }}%</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Completed Projects</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $summary['completed'] }}</h2>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="report-mda" class="mb-2 block text-sm font-semibold">MDA</label>
                    @if($lockMdaFilter)
                        <p id="report-mda" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900">
                            {{ $assignedMdaName }}
                        </p>
                    @else
                        <select id="report-mda"
                                name="mda_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                            <option value="">All MDAs</option>
                            @foreach($mdas as $mda)
                                <option value="{{ $mda->id }}" @selected((string) request('mda_id') === (string) $mda->id)>
                                    {{ $mda->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="report-partner" class="mb-2 block text-sm font-semibold">Development Partner</label>
                    <select id="report-partner"
                            name="partner_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" @selected((string) request('partner_id') === (string) $partner->id)>
                                {{ $partner->partner_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="report-status" class="mb-2 block text-sm font-semibold">Status</label>
                    <select id="report-status"
                            name="status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                        <option value="">All</option>
                        @foreach(['Planning', 'Ongoing', 'Completed', 'Suspended', 'Cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="report-sector" class="mb-2 block text-sm font-semibold">Sector</label>
                    <input type="text"
                           id="report-sector"
                           name="sector"
                           value="{{ request('sector') }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2"
                           placeholder="e.g. Health">
                </div>

                <div>
                    <label for="report-lga" class="mb-2 block text-sm font-semibold">LGA</label>
                    <input type="text"
                           id="report-lga"
                           name="lga"
                           value="{{ request('lga') }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2"
                           placeholder="e.g. Akure South">
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-700 px-6 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Filter Report
                </button>

                <a href="{{ route('reports.index') }}"
                   class="rounded-lg bg-gray-500 px-6 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Project</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">MDA</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Partner</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Sector</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">LGA</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Status</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Progress</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($projects as $project)
                        @php
                            $statusBadge = match ($project->status) {
                                'Planning' => 'bg-gray-100 text-gray-700',
                                'Ongoing' => 'bg-green-100 text-green-800',
                                'Completed' => 'bg-green-200 text-green-900',
                                'Suspended' => 'bg-amber-100 text-amber-900',
                                'Cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                            $progressWidth = min(max((int) $project->progress, 0), 100);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $project->project_title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->mda->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->developmentPartner->partner_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->sector }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->lga }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $project->status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-sm text-gray-700">{{ $project->progress }}%</span>
                                    <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200"
                                         role="progressbar"
                                         aria-valuenow="{{ $progressWidth }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-label="Progress for {{ $project->project_title }}">
                                        <div class="h-2 rounded-full bg-green-700" style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                @if($canOpenProject)
                                    <a href="{{ route('projects.show', $project->id) }}"
                                       class="inline-block rounded bg-green-700 px-3 py-1 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                        View
                                    </a>
                                @else
                                    <span class="text-gray-900">{{ $project->project_title }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

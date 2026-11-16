@extends('layouts.admin')

@section('title', $ownProfile ? 'My MDA Profile' : 'MDA Profile')

@section('content')

<div class="mx-auto max-w-6xl space-y-6">

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-green-800">
                    {{ $ownProfile ? 'Assigned MDA' : 'MDA profile' }}
                </p>
                <h2 class="mt-1 text-2xl font-bold text-green-900">{{ $mda->name }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $mda->code }}</p>
            </div>

            @if($mda->status)
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Inactive</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Lead projects</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['total'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Ongoing</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['ongoing'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Completed</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['completed'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Delayed</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['delayed'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Average progress</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['averageProgress'] }}%</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">On track (70%+)</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['onTrack'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Monitoring visits</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $performance['monitoringVisits'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow">
            <p class="text-sm text-gray-600">Assigned officers</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ $mda->officers_count }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Identity and contact</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->email ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Phone</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Contact person</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->contact_person ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->status ? 'Active' : 'Inactive' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm text-gray-500">Address</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->address ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Focal and alternate officers</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm text-gray-500">Focal person</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->focal_name ?: '-' }} {{ $mda->focal_designation ? '('.$mda->focal_designation.')' : '' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Department</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->focal_department ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Supervising director</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->focal_supervisor ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Focal phone</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->focal_phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Focal email</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->focal_email ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Alternate</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->alternate_name ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Alternate contact</dt>
                    <dd class="font-semibold text-gray-900">{{ $mda->alternate_phone ?: '-' }} / {{ $mda->alternate_email ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Assigned MDA officers</h3>
        <p class="mb-4 text-sm text-gray-600">Accounts with the MDA Officer role whose <span class="font-medium">mda_id</span> is this ministry. Assignment is managed by a Super Administrator.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-600">
                    <tr>
                        <th class="px-3 py-2 font-semibold" scope="col">Name</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($officers as $officer)
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $officer->name }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $officer->email }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-8 text-center text-sm text-gray-500">No MDA Officer account is assigned to this ministry yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Project portfolio</h3>
        <p class="mb-4 text-sm text-gray-600">Lead projects for this MDA. Participating-only records are not listed here.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-600">
                    <tr>
                        <th class="px-3 py-2 font-semibold" scope="col">Project</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Partner</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Status</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($portfolio as $project)
                        @php
                            $progressWidth = min(max((int) $project->progress, 0), 100);
                        @endphp
                        <tr>
                            <td class="px-3 py-3 text-sm font-medium text-gray-900">
                                @can('view', $project)
                                    <a href="{{ route('projects.show', $project) }}" class="text-green-800 underline">{{ $project->project_title }}</a>
                                @else
                                    {{ $project->project_title }}
                                @endcan
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $project->developmentPartner->partner_name ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm text-gray-700">
                                {{ $project->status ?: '-' }}
                                @if($project->initiative_status === 'Delayed')
                                    <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900">Delayed</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700">
                                <div class="flex items-center gap-2">
                                    <span>{{ $progressWidth }}%</span>
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-gray-200" role="progressbar" aria-valuenow="{{ $progressWidth }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress for {{ $project->project_title }}">
                                        <div class="h-2 rounded-full bg-green-700" style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500">No lead projects are recorded for this MDA.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Recent monitoring</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-600">
                    <tr>
                        <th class="px-3 py-2 font-semibold" scope="col">Date</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Project</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Officer</th>
                        <th class="px-3 py-2 font-semibold" scope="col">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentMonitorings as $visit)
                        <tr>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ optional($visit->monitoring_date)->format('d M Y') ?: '-' }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900">{{ $visit->project->project_title ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $visit->user->name ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $visit->status ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500">No monitoring visits are recorded for this MDA’s lead projects.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        @if($canManage)
            <a href="{{ route('mdas.edit', $mda) }}"
               class="rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Edit
            </a>
        @endif
        @if($ownProfile)
            <a href="{{ route('dashboard') }}"
               class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Back to dashboard
            </a>
        @else
            <a href="{{ route('mdas.index') }}"
               class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Back to directory
            </a>
        @endif
    </div>

</div>

@endsection

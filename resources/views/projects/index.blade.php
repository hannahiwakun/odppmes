@extends('layouts.admin')

@section('title', 'Projects')

@section('content')

@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800" role="status">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">

    <div class="flex flex-col gap-4 border-b border-gray-200 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-900">{{ auth()->user()->isMdaOfficer() ? 'My Projects' : 'Projects' }}</h2>
            <p class="mt-1 text-sm text-gray-600">
                @if(auth()->user()->isMdaOfficer())
                    Projects led by your assigned MDA. You can register and update these records.
                @else
                    Development projects recorded for Ondo State.
                @endif
            </p>
        </div>

        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Add Project
            </a>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Project Title</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">MDA</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Development Partner</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Sector</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Progress</th>
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
                        $progressWidth = min(max((int) ($project->progress ?? 0), 0), 100);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $project->id }}</td>
                        <td class="max-w-xs px-4 py-3 text-sm font-medium text-gray-900 break-words">{{ $project->project_title }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $project->mda->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $project->developmentPartner->partner_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $project->sector }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                {{ $project->status }}
                            </span>
                            @if(filled($project->initiative_status) && (string) $project->initiative_status !== (string) $project->status)
                                <p class="mt-1 text-xs text-gray-600">Register: {{ $project->initiative_status }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                <span>{{ $project->progress }}%</span>
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
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <a href="{{ route('projects.show', $project->id) }}"
                                   class="rounded bg-green-700 px-3 py-1 text-sm text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    View
                                </a>

                                <a href="{{ route('projects.edit', $project->id) }}"
                                   class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                    Edit
                                </a>

                                <a href="{{ route('project-documents.index', $project->id) }}"
                                   class="rounded bg-purple-700 px-3 py-1 text-sm text-white hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2">
                                    Documents
                                </a>

                                @can('delete', $project)
                                    <form action="{{ route('projects.destroy', $project->id) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Delete this project? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            No Projects Found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4">
        {{ $projects->links() }}
    </div>

</div>

@endsection

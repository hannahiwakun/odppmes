@extends('layouts.admin')

@section('title', 'Monitoring')

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
            <h2 class="text-2xl font-bold text-green-900">Monitoring</h2>
            <p class="mt-1 text-sm text-gray-600">Field visits recorded for Ondo State development projects.</p>
        </div>

        <a href="{{ route('monitorings.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Add Monitoring
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Project</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Photo</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Officer</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Date</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Status</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Progress</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">GPS</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($monitorings as $monitoring)
                    @php
                        $statusBadge = match ($monitoring->status) {
                            'Planning' => 'bg-gray-100 text-gray-700',
                            'Ongoing' => 'bg-green-100 text-green-800',
                            'Completed' => 'bg-green-200 text-green-900',
                            'Suspended' => 'bg-amber-100 text-amber-900',
                            'Cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                        $progressWidth = min(max((int) ($monitoring->progress_after ?? 0), 0), 100);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $monitoring->id }}</td>
                        <td class="max-w-xs px-4 py-3 text-sm font-semibold text-gray-900 break-words">
                            {{ $monitoring->project->project_title ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($monitoring->photo)
                                <a href="{{ route('monitorings.photo', $monitoring) }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ route('monitorings.photo', $monitoring) }}"
                                         alt="Monitoring photo"
                                         class="mx-auto h-16 w-16 rounded object-cover">
                                </a>
                            @else
                                <span class="text-sm text-gray-400">No Photo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $monitoring->user->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                {{ $monitoring->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-sm text-gray-700">{{ $monitoring->progress_after }}%</span>
                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200"
                                     role="progressbar"
                                     aria-valuenow="{{ $progressWidth }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     aria-label="Progress after visit {{ $monitoring->id }}">
                                    <div class="h-2 rounded-full bg-green-700" style="width: {{ $progressWidth }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">
                            {{ ($monitoring->latitude && $monitoring->longitude) ? 'GPS recorded' : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <a href="{{ route('monitorings.show', $monitoring->id) }}"
                                   class="rounded bg-green-700 px-3 py-1 text-sm text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    View
                                </a>

                                <a href="{{ route('monitorings.edit', $monitoring->id) }}"
                                   class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                    Edit
                                </a>

                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isMEO())
                                    <form action="{{ route('monitorings.destroy', $monitoring->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this monitoring record?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                            No monitoring records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4">
        {{ $monitorings->links() }}
    </div>

</div>

@endsection

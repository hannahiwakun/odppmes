@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')

@php
    $actionBadges = [
        'Created' => 'bg-green-100 text-green-800',
        'Updated' => 'bg-blue-100 text-blue-800',
        'Deleted' => 'bg-red-100 text-red-800',
        'Deactivated' => 'bg-amber-100 text-amber-900',
    ];
@endphp

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">

    <div class="border-b border-gray-200 p-6">
        <h2 class="text-2xl font-bold text-green-900">Audit Logs</h2>
        <p class="mt-1 text-sm text-gray-600">A record of activity in the portal. These entries cannot be edited or deleted here.</p>
    </div>

    <form method="GET" action="{{ route('audit.logs') }}" class="border-b border-gray-200 px-6 py-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-md">
                <label for="audit-search" class="mb-2 block text-sm font-semibold text-gray-700">Search</label>
                <input id="audit-search"
                       type="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search activity..."
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            </div>

            <button type="submit"
                    class="rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Search
            </button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">User</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Action</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Module</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Description</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="align-top hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $actionBadges[$log->action] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ $log->module }}
                        </td>
                        <td class="max-w-md px-4 py-3 text-sm text-gray-700">
                            <p class="max-w-md whitespace-normal break-words">{{ $log->description }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ filled($log->ip_address) ? $log->ip_address : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            No audit records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4">
        {{ $logs->links() }}
    </div>

</div>

@endsection

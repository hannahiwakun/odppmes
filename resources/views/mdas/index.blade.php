@extends('layouts.admin')

@section('title', 'MDAs')

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
            <h2 class="text-2xl font-bold text-green-900">MDA directory</h2>
            <p class="mt-1 text-sm text-gray-600">Ministries, departments and agencies recorded for Ondo State. Open a profile for officers, projects and performance.</p>
        </div>

        @can('create', App\Models\Mda::class)
            <a href="{{ route('mdas.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Add New MDA
            </a>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Code</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Contact</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Officers</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Projects</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Status</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($mdas as $mda)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('mdas.show', $mda) }}" class="text-green-800 underline">{{ $mda->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $mda->code }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div>{{ filled($mda->email) ? $mda->email : 'No email' }}</div>
                            <div class="text-gray-500">{{ filled($mda->phone) ? $mda->phone : 'No phone' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $mda->officers_count }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $mda->projects_count }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($mda->status)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <a href="{{ route('mdas.show', $mda) }}"
                                   class="rounded bg-green-700 px-3 py-1 text-sm text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    View
                                </a>

                                @can('update', $mda)
                                    <a href="{{ route('mdas.edit', $mda->id) }}"
                                       class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                        Edit
                                    </a>
                                @endcan

                                @can('deactivate', $mda)
                                    @if($mda->status)
                                        <form action="{{ route('mdas.deactivate', $mda) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Deactivate this MDA? Its projects and records will be kept.');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="rounded bg-amber-700 px-3 py-1 text-sm text-white hover:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
                                                Deactivate
                                            </button>
                                        </form>
                                    @endif
                                @endcan

                                @can('delete', $mda)
                                    <form action="{{ route('mdas.destroy', $mda->id) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this MDA?');">
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
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4">
        {{ $mdas->links() }}
    </div>

</div>

@endsection

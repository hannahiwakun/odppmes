@extends('layouts.admin')

@section('title', 'Development Partners')

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
            <h2 class="text-2xl font-bold text-green-900">Development Partners</h2>
            <p class="mt-1 text-sm text-gray-600">Organisations working with Ondo State on development projects.</p>
        </div>

        <a href="{{ route('development-partners.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Add Development Partner
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Partner Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Acronym</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Type</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Country</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Status</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($partners as $partner)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $partner->id }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $partner->partner_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ filled($partner->acronym) ? $partner->acronym : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $partner->partner_type }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ filled($partner->country) ? $partner->country : '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($partner->status)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <a href="{{ route('development-partners.show', $partner->id) }}"
                                   class="rounded bg-green-700 px-3 py-1 text-sm text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    View
                                </a>

                                <a href="{{ route('development-partners.edit', $partner->id) }}"
                                   class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                    Edit
                                </a>

                                <form action="{{ route('development-partners.destroy', $partner->id) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Delete this Development Partner?')"
                                            class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            No Development Partners Found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

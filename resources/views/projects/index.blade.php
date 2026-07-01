@extends('layouts.admin')

@section('title', 'Projects')

@section('content')

<div class="bg-white shadow rounded-lg p-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Projects
        </h2>

        <a href="{{ route('projects.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
            + Add Project
        </a>

    </div>

    @if(session('success'))

        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5">
            {{ session('success') }}
        </div>

    @endif

    <div class="overflow-x-auto">

        <table class="w-full border border-gray-200">

            <thead class="bg-gray-100">

                <tr>

                    <th class="border px-4 py-2">ID</th>

                    <th class="border px-4 py-2">Project Title</th>

                    <th class="border px-4 py-2">MDA</th>

                    <th class="border px-4 py-2">Development Partner</th>

                    <th class="border px-4 py-2">Sector</th>

                    <th class="border px-4 py-2">Status</th>

                    <th class="border px-4 py-2">Progress</th>

                    <th class="border px-4 py-2">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($projects as $project)

                <tr>

                    <td class="border px-4 py-2">{{ $project->id }}</td>

                    <td class="border px-4 py-2">{{ $project->project_title }}</td>

                    <td class="border px-4 py-2">{{ $project->mda->name }}</td>

                    <td class="border px-4 py-2">
                        {{ $project->developmentPartner->partner_name ?? '-' }}
                    </td>

                    <td class="border px-4 py-2">{{ $project->sector }}</td>

                    <td class="border px-4 py-2">{{ $project->status }}</td>

                    <td class="border px-4 py-2">{{ $project->progress }}%</td>

                    <td class="px-4 py-2">

    <a
        href="{{ route('projects.edit', $project->id) }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">

        Edit

    </a>

    <form
        action="{{ route('projects.destroy', $project->id) }}"
        method="POST"
        style="display:inline;">

        @csrf
        @method('DELETE')

        <button
            onclick="return confirm('Are you sure you want to delete this project?')"
            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">

            Delete

        </button>

    </form>

</td>

                </tr>

                @empty

                <tr>

                    <td colspan="8" class="text-center py-5 text-gray-500">
                        No Projects Found
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-5">

        {{ $projects->links() }}

    </div>

</div>

@endsection
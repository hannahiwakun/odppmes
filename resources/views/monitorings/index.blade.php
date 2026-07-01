@extends('layouts.admin')

@section('title', 'Monitoring')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Project Monitoring
        </h2>

        <a href="{{ route('monitorings.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

            + New Monitoring

        </a>

    </div>

    @if(session('success'))

        <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded mb-5">

            {{ session('success') }}

        </div>

    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">

        <table class="min-w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left">ID</th>

                    <th class="px-4 py-3 text-left">Project</th>
                    <th class="px-4 py-3">Photo</th>
                    <th class="px-4 py-3 text-left">Officer</th>

                    <th class="px-4 py-3 text-left">Monitoring Date</th>

                    <th class="px-4 py-3 text-left">Status</th>

                    <th class="px-4 py-3 text-left">Progress</th>

                    <th class="px-4 py-3 text-left">Action</th>

                </tr>

            </thead>

            <tbody>

            @forelse($monitorings as $monitoring)

                <tr class="border-t">

                    <td class="px-4 py-3">
                        {{ $monitoring->id }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $monitoring->project->project_title }}
                    </td>

<td class="px-4 py-3">

    @if($monitoring->photo)

        <a href="{{ asset('storage/'.$monitoring->photo) }}" target="_blank">

            <img
                src="{{ asset('storage/'.$monitoring->photo) }}"
                class="w-20 h-16 object-cover rounded border">

        </a>

    @else

        <span class="text-gray-400">
            No Photo
        </span>

    @endif

</td>
                    <td class="px-4 py-3">
                        {{ $monitoring->user->name }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $monitoring->monitoring_date }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $monitoring->status }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $monitoring->progress_after }}%
                    </td>

                   <td class="px-4 py-3">

    <a href="{{ route('monitorings.edit', $monitoring->id) }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">

        Edit

    </a>

    <form action="{{ route('monitorings.destroy', $monitoring->id) }}"
          method="POST"
          style="display:inline;">

        @csrf
        @method('DELETE')

        <button
            type="submit"
            onclick="return confirm('Are you sure you want to delete this monitoring record?')"
            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">

            Delete

        </button>

    </form>

</td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="text-center py-6 text-gray-500">

                        No Monitoring Records Found

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
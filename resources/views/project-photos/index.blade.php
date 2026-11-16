@extends('layouts.admin')

@section('title', 'Project Gallery')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">

    @if(session('success'))
        <div class="rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-green-900">Project Gallery</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $project->project_title }}</p>
            </div>

            <a href="{{ route('projects.photos.create', $project) }}"
               class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Upload Photo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @forelse($photos as $photo)
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
                <img src="{{ route('projects.photos.file', [$project, $photo]) }}"
                     alt="{{ filled($photo->caption) ? $photo->caption : 'Project photo' }}"
                     class="h-56 w-full object-cover">

                <div class="p-4">
                    <p class="font-semibold text-gray-900">{{ $photo->caption ?: 'Project Photo' }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        Uploaded by: {{ $photo->uploader->name ?? '-' }}
                    </p>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isMEO())
                        <form action="{{ route('projects.photos.destroy', [$project, $photo]) }}"
                              method="POST"
                              class="mt-3"
                              onsubmit="return confirm('Delete this photo?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-10 text-center text-gray-500 shadow md:col-span-3">
                No project photos uploaded yet.
            </div>
        @endforelse
    </div>

</div>

@endsection

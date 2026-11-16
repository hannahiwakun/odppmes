@extends('layouts.admin')

@section('title', 'Upload Project Photo')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="mx-auto max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Upload Project Photo</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">{{ $project->project_title }}</p>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('projects.photos.store', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-5">
            <label for="project-photo-file" class="mb-2 block font-semibold">Select Photo</label>
            <input type="file"
                   id="project-photo-file"
                   name="photo"
                   accept="image/*"
                   class="{{ $fieldClass }}">
            @error('photo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-5">
            <label for="project-photo-caption" class="mb-2 block font-semibold">
                Caption <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="project-photo-caption"
                   name="caption"
                   value="{{ old('caption') }}"
                   class="{{ $fieldClass }}"
                   placeholder="Example: Site inspection at PHC 115">
            @error('caption')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Upload Photo
            </button>

            <a href="{{ route('projects.photos.index', $project) }}"
               class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Back
            </a>
        </div>
    </form>

</div>

@endsection

@extends('layouts.cms')
@section('title', $profile->full_name)
@section('content')
@include('cms.partials.flash')
<div class="max-w-4xl space-y-4 rounded-lg border bg-white p-6 shadow">
    <div class="flex justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-900">{{ $profile->full_name }}</h2>
            <p class="text-gray-700">{{ $profile->designation }}</p>
        </div>
        @can('update', $profile)
            <a href="{{ route('cms.management-profiles.edit', $profile) }}" class="cms-btn cms-btn-primary">Edit</a>
        @endcan
    </div>
    @if($profile->photo)
        <img src="{{ route('cms.media.preview', $profile->photo) }}" alt="{{ $profile->full_name }}" class="h-40 w-40 rounded-lg object-cover">
        <p class="text-sm text-gray-600">{{ $profile->photo->original_name }}</p>
        @include('cms.partials.clear-media', ['asset' => $profile->photo])
    @endif
    <p class="whitespace-pre-wrap">{{ $profile->biography }}</p>
    @if($profile->responsibilities)
        <p class="whitespace-pre-wrap text-gray-700">{{ $profile->responsibilities }}</p>
    @endif
    @include('cms.partials.workflow-actions', ['content' => $profile, 'type' => 'management-profiles'])
</div>
@endsection

@extends('layouts.cms')
@section('title', $partner->name)
@section('content')
@include('cms.partials.flash')
<div class="max-w-4xl space-y-4 rounded-lg border bg-white p-6 shadow">
    <div class="flex justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-900">{{ $partner->name }}</h2>
            <p class="text-gray-700">{{ $partner->category }}</p>
        </div>
        @can('update', $partner)
            <a href="{{ route('cms.public-partners.edit', $partner) }}" class="cms-btn cms-btn-primary">Edit</a>
        @endcan
    </div>
    <p class="whitespace-pre-wrap">{{ $partner->summary }}</p>
    @if($partner->website_url)
        <p class="text-sm">Website: {{ $partner->website_url }}</p>
    @endif
    @if($partner->logo)
        <img src="{{ route('cms.media.preview', $partner->logo) }}" alt="{{ $partner->name }}" class="h-24 w-24 rounded object-contain bg-green-50 p-2">
        <p class="text-sm text-gray-600">{{ $partner->logo->original_name }}</p>
        @include('cms.partials.clear-media', ['asset' => $partner->logo])
    @endif
    @include('cms.partials.workflow-actions', ['content' => $partner, 'type' => 'public-partners'])
</div>
@endsection

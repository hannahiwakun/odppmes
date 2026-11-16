@extends('layouts.cms')
@section('title', $banner->title)
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-4xl space-y-6 p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-green-800">{{ $banner->typeLabel() }}</p>
            <h2 class="text-2xl font-bold text-green-950">{{ $banner->title }}</h2>
            <p class="mt-2">@include('cms.partials.status', ['status' => $banner->status])</p>
        </div>
        <div class="cms-actions">
            @can('update', $banner)
                <a href="{{ route('cms.homepage-banners.edit', $banner) }}" class="cms-btn cms-btn-primary">Edit</a>
            @endcan
        </div>
    </div>

    <dl class="grid gap-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="font-semibold text-gray-800">Author</dt>
            <dd>{{ $banner->createdBy?->name ?? 'Not recorded' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Display order</dt>
            <dd>{{ $banner->sort_order }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Button text</dt>
            <dd>{{ $banner->button_text ?: 'Not set' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Destination URL</dt>
            <dd class="break-all">{{ $banner->link_url ?: 'Not set' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Visible from</dt>
            <dd>{{ $banner->published_from?->format('d M Y H:i') ?? 'On publish' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Visible until</dt>
            <dd>{{ $banner->published_until?->format('d M Y H:i') ?? 'No end date' }}</dd>
        </div>
    </dl>

    @if ($banner->body)
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Supporting text</h3>
            <p class="mt-1 whitespace-pre-wrap text-gray-800">{{ $banner->body }}</p>
        </div>
    @endif

    @if ($banner->image)
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Banner image</h3>
            <img src="{{ route('cms.media.preview', $banner->image) }}" alt="{{ $banner->imageAltText() }}" class="mt-2 max-h-64 w-full rounded-lg border border-green-100 bg-green-50 object-contain">
            @if ($banner->image_alt)
                <p class="mt-1 text-sm text-gray-600">{{ $banner->image_alt }}</p>
            @endif
            <p class="mt-1 text-xs text-gray-500">{{ $banner->image->original_name }}</p>
            @include('cms.partials.clear-media', ['asset' => $banner->image])
        </div>
    @else
        <p class="text-sm text-gray-600">No featured image is attached.</p>
    @endif

    @include('cms.partials.workflow-actions', ['content' => $banner, 'type' => 'homepage-banners'])
    @can('delete', $banner)
        <form method="POST" action="{{ route('cms.homepage-banners.destroy', $banner) }}" onsubmit="return confirm('Remove this homepage banner?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="cms-btn cms-btn-danger">Remove banner</button>
        </form>
    @endcan
</div>
@endsection

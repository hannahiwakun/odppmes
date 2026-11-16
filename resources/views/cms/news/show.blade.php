@extends('layouts.cms')

@section('title', $article->title)

@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-4xl space-y-6 p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-green-800">{{ $article->categoryLabel() }}</p>
            <h2 class="text-2xl font-bold text-green-950">{{ $article->title }}</h2>
            <p class="mt-1 text-sm text-gray-600">Slug: {{ $article->slug }}</p>
            <p class="mt-2">@include('cms.partials.status', ['status' => $article->status])</p>
        </div>
        <div class="cms-actions">
            @can('view', $article)
                <a href="{{ route('cms.news.preview', $article) }}" class="cms-btn cms-btn-secondary">Preview</a>
            @endcan
            @can('update', $article)
                <a href="{{ route('cms.news.edit', $article) }}" class="cms-btn cms-btn-primary">Edit</a>
            @endcan
        </div>
    </div>

    <dl class="grid gap-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="font-semibold text-gray-800">Author</dt>
            <dd>{{ $article->createdBy?->name ?? 'Not recorded' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Last updated</dt>
            <dd>{{ $article->updated_at?->format('d M Y H:i') }}</dd>
        </div>
    </dl>

    @if ($article->featuredImage)
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Featured image</h3>
            <img src="{{ route('cms.media.preview', $article->featuredImage) }}" alt="{{ $article->featured_image_alt ?: $article->title }}" class="mt-2 max-h-64 rounded-lg border border-green-100 object-cover">
            @if ($article->featured_image_alt)
                <p class="mt-1 text-sm text-gray-600">{{ $article->featured_image_alt }}</p>
            @endif
            <p class="mt-1 text-xs text-gray-500">{{ $article->featuredImage->is_cleared_for_public ? 'Cleared for public use' : 'Not yet cleared for public use' }}</p>
        </div>
    @endif

    <div>
        <h3 class="text-sm font-semibold text-gray-800">Excerpt</h3>
        <p class="mt-1 text-gray-800">{{ $article->excerpt }}</p>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-800">Article body</h3>
        <x-cms.news-body class="mt-2 text-gray-800" :source="$article->body" />
    </div>
    <p class="text-sm text-gray-600">
        Publication window:
        {{ $article->published_from?->format('d M Y H:i') ?? 'not set' }}
        —
        {{ $article->published_until?->format('d M Y H:i') ?? 'not set' }}
    </p>
    @include('cms.partials.workflow-actions', ['content' => $article, 'type' => 'news'])
    @can('delete', $article)
        <form method="POST" action="{{ route('cms.news.destroy', $article) }}" onsubmit="return confirm('Remove this news article? This cannot be undone from the listing.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="cms-btn cms-btn-danger">Remove article</button>
        </form>
    @endcan
</div>
@endsection

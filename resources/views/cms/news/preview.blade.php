@extends('layouts.cms')

@section('title', 'Preview: '.$article->title)

@section('content')
<div class="space-y-4">
    <div class="cms-card border-l-4 border-l-green-700 p-4">
        <p class="text-sm font-semibold text-green-950">Staff preview</p>
        <p class="cms-lede">This page is only visible to signed-in CMS users. It does not publish the article and is not a public web address.</p>
        <p class="mt-2">@include('cms.partials.status', ['status' => $article->status])</p>
        <div class="cms-actions mt-4">
            <a href="{{ route('cms.news.show', $article) }}" class="cms-btn cms-btn-secondary">Return to article</a>
            @can('update', $article)
                <a href="{{ route('cms.news.edit', $article) }}" class="cms-btn cms-btn-primary">Edit</a>
            @endcan
        </div>
    </div>

    <article class="cms-card cms-news-preview mx-auto p-5 sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-wide text-green-800">{{ $article->categoryLabel() }}</p>
        @if ($article->featuredImage)
            <img src="{{ route('cms.media.preview', $article->featuredImage) }}" alt="{{ $article->featured_image_alt ?: $article->title }}" class="cms-news-featured mt-4">
        @endif
        <h2 class="mt-5 text-2xl font-bold leading-tight text-green-950 sm:text-3xl">{{ $article->title }}</h2>
        <p class="mt-3 text-base leading-relaxed text-gray-700">{{ $article->excerpt }}</p>
        <p class="mt-3 text-sm text-gray-600">
            @if($article->createdBy)
                By {{ $article->createdBy->name }}
            @endif
            @if($article->published_at)
                · Published {{ $article->published_at->format('d F Y') }}
            @else
                · Not yet published
            @endif
            @if($article->published_from)
                · Visible from {{ $article->published_from->format('d M Y H:i') }}
            @endif
            @if($article->published_until)
                · Until {{ $article->published_until->format('d M Y H:i') }}
            @endif
        </p>
        <x-cms.news-body class="mt-6 text-gray-800" :source="$article->body" />
    </article>
</div>
@endsection

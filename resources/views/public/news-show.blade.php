@extends('layouts.public')

@section('title', ($fromCms ? $article->title : $article['title']).' — DPMO')

@section('content')
@php
    $title = $fromCms ? $article->title : $article['title'];
    $date = $fromCms
        ? optional($article->published_at)->format('d F Y')
        : \Illuminate\Support\Carbon::parse($article['date'])->format('d F Y');
    $body = $fromCms ? $article->body : $article['body'];
    $excerpt = $fromCms ? $article->excerpt : ($article['excerpt'] ?? '');
    $category = $fromCms ? $article->categoryLabel() : 'Announcements';
    $author = $fromCms ? $article->createdBy?->name : null;
    $draft = ! $fromCms && ! empty($article['draft']);
@endphp
@include('public.partials.page-hero', [
    'title' => $title,
    'lede' => $date ? $category.' · '.$date : $category,
])

<section class="public-section">
    <div class="public-wrap public-news-layout">
        <article class="public-news-article">
            @if ($draft)
                <span class="public-draft">Draft notice</span>
            @endif
            @include('public.partials.news-featured-image', [
                'url' => $fromCms ? $article->featuredImage?->publicUrl() : null,
                'alt' => $fromCms ? ($article->featured_image_alt ?: $article->title) : $title,
                'variant' => 'article',
            ])
            <p class="public-meta" style="margin-top:0.9rem;">
                {{ $category }}
                @if ($date)
                    · {{ $date }}
                @endif
                @if ($author)
                    · By {{ $author }}
                @endif
            </p>
            @if ($excerpt !== '')
                <p class="public-news-excerpt">{{ $excerpt }}</p>
            @endif
            <div class="public-prose">
                @if ($fromCms)
                    <x-cms.news-body :source="$body" />
                @else
                    @foreach (preg_split("/\n\n/", $body) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif
            </div>
            @php
                $url = url()->current();
                $shareText = rawurlencode($title);
            @endphp
            <p class="public-meta" style="margin-top:1.25rem;">Share:
                <a href="mailto:?subject={{ $shareText }}&body={{ rawurlencode($url) }}">Email</a>
                ·
                <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ rawurlencode($url) }}" rel="noopener noreferrer">X</a>
                ·
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" rel="noopener noreferrer">Facebook</a>
            </p>
        </article>
        <aside class="public-news-related">
            <h2>Related news</h2>
            @if ($fromCms)
                @forelse ($related as $item)
                    <article class="public-card" style="margin-top:0.75rem;">
                        <p class="public-meta">{{ optional($item->published_at)->format('d F Y') }}</p>
                        <h3><a href="{{ route('public.news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                    </article>
                @empty
                    <p class="lede">No related articles yet.</p>
                @endforelse
            @else
                @forelse ($related as $item)
                    <article class="public-card" style="margin-top:0.75rem;">
                        <p class="public-meta">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d F Y') }}</p>
                        <h3><a href="{{ route('public.news.show', $item['slug']) }}">{{ $item['title'] }}</a></h3>
                    </article>
                @empty
                    <p class="lede">No related articles yet.</p>
                @endforelse
            @endif
        </aside>
    </div>
</section>
@endsection

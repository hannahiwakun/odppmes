@extends('layouts.public')

@section('title', 'News & Updates — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'News and updates',
    'lede' => 'Announcements from the Development Partners Management Office.',
])

<section class="public-section">
    <div class="public-wrap">
        <form method="GET" action="{{ route('public.news') }}" class="public-card" style="margin-bottom:1.25rem;">
            <div style="display:flex;flex-wrap:wrap;gap:0.8rem;align-items:end;">
                <div style="flex:1;min-width:12rem;">
                    <label for="q">Search published articles</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" style="width:100%;margin-top:0.4rem;">
                </div>
                <div style="min-width:12rem;">
                    <label for="category">Category</label>
                    <select id="category" name="category" style="width:100%;margin-top:0.4rem;">
                        <option value="">All categories</option>
                        @foreach (\App\Cms\CmsNewsCategories::all() as $categoryOption)
                            <option value="{{ $categoryOption }}" @selected(request('category') === $categoryOption)>{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="public-btn public-btn-primary">Search</button>
            </div>
        </form>

        @if ($usingCms)
            <div class="public-grid public-grid-2">
                @forelse ($articles as $article)
                    <article class="public-card">
                        @include('public.partials.news-featured-image', [
                            'url' => $article->featuredImage?->publicUrl(),
                            'alt' => $article->featured_image_alt ?: $article->title,
                        ])
                        <p class="public-meta" style="margin-top:0.7rem;">{{ $article->categoryLabel() }} · {{ optional($article->published_at)->format('d F Y') }}</p>
                        <h3><a href="{{ route('public.news.show', $article->slug) }}">{{ $article->title }}</a></h3>
                        <p>{{ $article->excerpt }}</p>
                        <a href="{{ route('public.news.show', $article->slug) }}" class="public-btn public-btn-outline" style="margin-top:0.6rem;">Read more</a>
                    </article>
                @empty
                    <p class="lede">No published articles match this search.</p>
                @endforelse
            </div>
            <div style="margin-top:1rem;">{{ $articles->links() }}</div>
        @else
            <div class="public-grid public-grid-2">
                @foreach ($fallbackArticles as $article)
                    <article class="public-card">
                        @include('public.partials.news-featured-image')
                        @if (! empty($article['draft']))
                            <span class="public-draft">Draft notice</span>
                        @endif
                        <p class="public-meta" style="margin-top:0.7rem;">Announcements · {{ \Illuminate\Support\Carbon::parse($article['date'])->format('d F Y') }}</p>
                        <h3><a href="{{ route('public.news.show', $article['slug']) }}">{{ $article['title'] }}</a></h3>
                        <p>{{ $article['excerpt'] }}</p>
                        <a href="{{ route('public.news.show', $article['slug']) }}" class="public-btn public-btn-outline" style="margin-top:0.6rem;">Read more</a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

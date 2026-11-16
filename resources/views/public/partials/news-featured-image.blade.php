@php
    $alt = $alt ?? '';
    $url = $url ?? null;
    $variant = $variant ?? 'card';
    $isArticle = $variant === 'article';
    $cardHeight = $height ?? '12rem';
@endphp
@if ($url)
    <img
        src="{{ $url }}"
        alt="{{ $alt }}"
        width="960"
        height="540"
        class="{{ $isArticle ? 'news-featured-article' : 'news-featured-card' }}"
        @unless ($isArticle) style="height: {{ $cardHeight }};" @endunless
    >
@else
    <div class="public-media-ph {{ $isArticle ? 'news-featured-article-ph' : 'news-featured-card-ph' }}" @unless ($isArticle) style="height: {{ $cardHeight }};" @endunless>Featured image not yet available</div>
@endif

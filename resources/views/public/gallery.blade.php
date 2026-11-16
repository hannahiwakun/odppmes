@extends('layouts.public')

@section('title', 'Gallery — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Gallery',
    'lede' => 'Official photographs of DPMO activities. This page does not invent images of officials or present stock photographs as Office events.',
])

<section class="public-section">
    <div class="public-wrap">
        @if ($albums->isNotEmpty())
            <nav class="public-card" style="margin-bottom:1.25rem;" aria-label="Gallery albums">
                <p class="public-meta" style="margin-bottom:0.6rem;">Jump to an album</p>
                <ul style="display:flex;flex-wrap:wrap;gap:0.5rem 0.85rem;margin:0;padding:0;list-style:none;">
                    @foreach ($albums as $album)
                        <li><a href="#album-{{ $album->slug }}">{{ $album->title }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <h2>Albums</h2>
            <div class="public-grid public-grid-3" style="margin-top:0.85rem;">
                @foreach ($albums as $album)
                    @php
                        $cover = $album->publicItems->first(fn ($item) => $item->media?->publicUrl());
                    @endphp
                    <article class="public-card">
                        @if ($cover?->media?->publicUrl())
                            <img src="{{ $cover->media->publicUrl() }}" alt="{{ $cover->caption ?: $album->title }}" width="480" height="320" style="width:100%;height:12rem;object-fit:cover;border-radius:0.5rem;">
                        @else
                            <div class="public-media-ph" role="img" aria-label="No public photograph in this album">Official photographs will appear when they have been published and cleared for public use</div>
                        @endif
                        <h3 style="margin-top:0.75rem;">{{ $album->title }}</h3>
                        <p>{{ $album->description }}</p>
                        <p class="public-meta">{{ $album->publicItems->filter(fn ($item) => $item->media?->publicUrl())->count() }} public photograph(s)</p>
                        <a href="#album-{{ $album->slug }}" class="public-btn public-btn-outline" style="margin-top:0.65rem;">View photographs</a>
                    </article>
                @endforeach
            </div>
            <div style="margin-top:1rem;">{{ $albums->links() }}</div>

            <h2 style="margin-top:2rem;">Photographs</h2>
            @foreach ($albums as $album)
                @php
                    $visible = $album->publicItems->filter(fn ($item) => $item->media?->publicUrl());
                @endphp
                <section id="album-{{ $album->slug }}" class="public-card" style="margin-top:1rem;scroll-margin-top:6rem;">
                    <h3>{{ $album->title }}</h3>
                    <p>{{ $album->description }}</p>
                    @if ($visible->isEmpty())
                        <p class="public-meta">No photographs in this album have been cleared for public use.</p>
                    @else
                        <div class="public-grid public-grid-3" style="margin-top:0.75rem;">
                            @foreach ($visible as $item)
                                <figure style="margin:0;">
                                    <img src="{{ $item->media->publicUrl() }}" alt="{{ $item->caption ?: $album->title }}" width="480" height="320" style="width:100%;height:12rem;object-fit:cover;border-radius:0.5rem;">
                                    @if ($item->caption)
                                        <figcaption class="public-meta">{{ $item->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        @else
            <p class="public-note">No official gallery files have been supplied for publication. Albums are reserved so photographs can be added when the Office provides them. Images stored against confidential project records in ODPPMES are not displayed here.</p>
            <div class="public-grid public-grid-3">
                @foreach ($site['gallery_albums'] as $album)
                    <article class="public-card">
                        <div class="public-media-ph">No photographs or videos in this album yet</div>
                        <h3 style="margin-top:0.75rem;">{{ $album }}</h3>
                        <p class="public-meta">Awaiting official media from the Office.</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

@extends('layouts.public')

@section('title', 'DPMO — Ondo State Development Partners Management Office')

@section('content')
@php
    $primaryHero = $heroBanners->first();
    $extraHeroes = $heroBanners->slice(1);
@endphp
@if ($alertBanners->isNotEmpty())
    <div class="public-banner-alerts">
        @foreach ($alertBanners as $banner)
            <div class="public-banner-alert">
                <div class="public-wrap public-banner-alert-inner">
                    <div>
                        <p class="public-banner-kicker">Alert</p>
                        <p class="public-banner-alert-title">{{ $banner->title }}</p>
                        @if ($banner->body)
                            <p class="public-banner-alert-text">{{ $banner->body }}</p>
                        @endif
                    </div>
                    @include('public.partials.banner-cta', ['banner' => $banner, 'class' => 'public-btn public-banner-alert-btn'])
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($primaryHero)
    <section class="public-hero">
        <div class="public-wrap">
            <div class="public-hero-grid">
                <div>
                    <p class="public-eyebrow">Hero banner</p>
                    <h1>{{ $primaryHero->title }}</h1>
                    @if ($primaryHero->body)
                        <p class="intro">{{ $primaryHero->body }}</p>
                    @endif
                    <div class="public-actions">
                        @include('public.partials.banner-cta', ['banner' => $primaryHero, 'class' => 'public-btn public-btn-primary'])
                    </div>
                </div>
                <figure class="public-hero-media">
                    <div class="public-hero-frame public-hero-frame-cms">
                        @include('public.partials.banner-image', [
                            'banner' => $primaryHero,
                            'class' => 'public-banner-hero-image',
                            'placeholderClass' => 'public-banner-hero-ph',
                        ])
                    </div>
                </figure>
            </div>
        </div>
    </section>
@else
    <section class="public-hero">
        <div class="public-wrap">
            <div class="public-hero-grid">
                <div>
                    <p class="public-eyebrow">{{ $site['hero']['eyebrow'] }}</p>
                    <h1>{{ $site['hero']['title'] }}</h1>
                    <p class="intro">{{ $site['hero']['intro'] }}</p>
                    <div class="public-actions">
                        <a href="{{ route('public.about') }}" class="public-btn public-btn-primary">About the Office</a>
                        <a href="{{ route('login') }}" class="public-btn public-btn-hero">Portal Login</a>
                    </div>
                </div>
                <figure class="public-hero-media">
                    <div class="public-hero-frame">
                        <img src="{{ asset('images/hero-development.jpg') }}"
                             width="700"
                             height="520"
                             alt="Illustrative photograph of construction and development work. Not a DPMO activity or an Ondo State official event.">
                        <aside class="public-hero-panel" aria-label="Office at a glance">
                            <h2>What DPMO does</h2>
                            <ul>
                                <li>Coordinates development partners and MDAs</li>
                                <li>Supports monitoring through ODPPMES</li>
                                <li>Facilitates stakeholder engagement</li>
                                <li>Reports on partner-supported programmes</li>
                            </ul>
                        </aside>
                    </div>
                    <figcaption class="public-hero-caption">Illustrative photograph from Unsplash (free licence). It is not a picture of DPMO staff, partners or an official Ondo State event.</figcaption>
                </figure>
            </div>
        </div>
    </section>
@endif

@foreach ($extraHeroes as $banner)
    <section class="public-section public-wash">
        <div class="public-wrap public-banner-hero-extra">
            <div>
                <p class="public-kicker">Hero banner</p>
                <h2>{{ $banner->title }}</h2>
                @if ($banner->body)
                    <p class="lede">{{ $banner->body }}</p>
                @endif
                <div class="public-actions">
                    @include('public.partials.banner-cta', ['banner' => $banner])
                </div>
            </div>
            @include('public.partials.banner-image', ['banner' => $banner])
        </div>
    </section>
@endforeach

@if ($featureBanners->isNotEmpty())
<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">Highlights</p>
        <h2>Featured development notices</h2>
        <div class="public-grid public-grid-2">
            @foreach ($featureBanners as $banner)
                <article class="public-card">
                    @include('public.partials.banner-image', [
                        'banner' => $banner,
                        'class' => 'news-featured-card',
                        'placeholderClass' => 'news-featured-card-ph',
                    ])
                    <p class="public-meta" style="margin-top:0.7rem;">Feature Banner</p>
                    <h3>{{ $banner->title }}</h3>
                    @if ($banner->body)
                        <p>{{ $banner->body }}</p>
                    @endif
                    <div class="public-actions">
                        @include('public.partials.banner-cta', ['banner' => $banner, 'class' => 'public-btn public-btn-outline'])
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">About the Office</p>
        <h2>The Development Partners Management Office</h2>
        <p class="lede">{{ $site['about_brief'] }}</p>
        <div class="public-grid public-grid-2">
            <article class="public-card">
                <p class="public-kicker">Direction</p>
                <h3>Vision</h3>
                <p>{{ $site['vision']['text'] }}</p>
            </article>
            <article class="public-card">
                <p class="public-kicker">Purpose</p>
                <h3>Mission</h3>
                <p>{{ $site['mission']['text'] }}</p>
            </article>
        </div>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        <p class="public-kicker">What we support</p>
        <h2>Core services</h2>
        <p class="lede">DPMO supports the State through partnership coordination, resource mobilisation, programme facilitation and monitoring. The full set of official functions is on the Services page.</p>
        <div class="public-grid public-grid-4">
            @foreach (array_slice($site['services'], 0, 4) as $service)
                <article class="public-card">
                    <div class="public-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="22" height="22">{!! $service['icon'] !!}</svg>
                    </div>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['text'] }}</p>
                </article>
            @endforeach
        </div>
        <div class="public-actions">
            <a href="{{ route('public.services') }}" class="public-btn public-btn-outline">All services</a>
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">Cooperation</p>
        <h2>Development partners</h2>
        <p class="lede">{{ $site['stakeholder_section']['title'] }} are listed for public information. Named organisations are published only after the Office confirms them as official partners.</p>
        <div class="public-grid public-grid-3">
            @forelse ($homePartners as $partner)
                <article class="public-card">
                    <h3>{{ $partner->name }}</h3>
                    <p>{{ $partner->summary }}</p>
                    <p class="public-meta">{{ $partner->category }}</p>
                </article>
            @empty
                @foreach (array_slice($site['partner_categories'], 0, 3) as $category)
                    <article class="public-card">
                        <span class="public-draft">Stakeholder category</span>
                        <h3>{{ $category['title'] }}</h3>
                        <p>{{ $category['text'] }}</p>
                    </article>
                @endforeach
            @endforelse
        </div>
        <div class="public-actions">
            <a href="{{ route('public.partners') }}" class="public-btn public-btn-outline">Stakeholders and partners</a>
        </div>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        <p class="public-kicker">Public summaries</p>
        <h2>Featured programmes</h2>
        <p class="lede">Public highlights only. Confidential project files, budgets and monitoring records stay in the authorised portal.</p>
        <div class="public-grid public-grid-3">
            @forelse ($homeHighlights as $highlight)
                <article class="public-card">
                    <h3>{{ $highlight->title }}</h3>
                    <p>{{ $highlight->summary }}</p>
                    <p class="public-meta">{{ $highlight->sector }} · {{ $highlight->public_status }}</p>
                </article>
            @empty
                @foreach ($site['featured_programmes'] as $programme)
                    <article class="public-card">
                        <div class="public-media-ph">Photograph not yet supplied for public use</div>
                        @if (! empty($programme['draft']))
                            <span class="public-draft">Editable draft — not a published project record</span>
                        @endif
                        <h3>{{ $programme['title'] }}</h3>
                        <p>{{ $programme['description'] }}</p>
                        <p class="public-meta">{{ $programme['sector'] }} · {{ $programme['status'] }}</p>
                    </article>
                @endforeach
            @endforelse
        </div>
        <div class="public-actions">
            <a href="{{ route('public.projects') }}" class="public-btn public-btn-outline">Projects &amp; programmes</a>
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">Coverage</p>
        <h2>Key development sectors</h2>
        <div class="public-grid public-grid-4">
            @foreach ($site['sectors'] as $sector)
                <article class="public-card"><h3>{{ $sector }}</h3></article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        <div class="public-grid public-grid-3">
            <div>
                <p class="public-kicker">Updates</p>
                <h2>News</h2>
                @forelse ($homeNews as $article)
                    <article class="public-card" style="margin-top:0.75rem;">
                        @include('public.partials.news-featured-image', [
                            'url' => $article->featuredImage?->publicUrl(),
                            'alt' => $article->featured_image_alt ?: $article->title,
                            'height' => '8rem',
                        ])
                        <p class="public-meta" style="margin-top:0.6rem;">{{ $article->categoryLabel() }} · {{ optional($article->published_at)->format('d F Y') }}</p>
                        <h3><a href="{{ route('public.news.show', $article->slug) }}">{{ $article->title }}</a></h3>
                        <p>{{ $article->excerpt }}</p>
                    </article>
                @empty
                    @foreach (array_slice($site['news'], 0, 2) as $article)
                        <article class="public-card" style="margin-top:0.75rem;">
                            <div class="public-media-ph">Featured image not yet supplied</div>
                            @if (! empty($article['draft']))
                                <span class="public-draft">Draft notice</span>
                            @endif
                            <h3><a href="{{ route('public.news.show', $article['slug']) }}">{{ $article['title'] }}</a></h3>
                            <p class="public-meta">{{ \Illuminate\Support\Carbon::parse($article['date'])->format('d F Y') }}</p>
                            <p>{{ $article['excerpt'] }}</p>
                        </article>
                    @endforeach
                @endforelse
                <div class="public-actions">
                    <a href="{{ route('public.news') }}" class="public-btn public-btn-outline">All news</a>
                </div>
            </div>
            <div>
                <p class="public-kicker">Media</p>
                <h2>Gallery</h2>
                <article class="public-card">
                    @if ($homeAlbums->isNotEmpty())
                        @php $preview = $homeAlbums->first()->publicItems->first()?->media; @endphp
                        @if ($preview?->publicUrl())
                            <img src="{{ $preview->publicUrl() }}" alt="" width="480" height="240" style="width:100%;height:10rem;object-fit:cover;border-radius:0.5rem;">
                        @endif
                        <h3>{{ $homeAlbums->first()->title }}</h3>
                        <p>{{ $homeAlbums->first()->description }}</p>
                    @else
                        <div class="public-media-ph">Official gallery photographs will appear here when supplied by the Office</div>
                        <p>This website does not use stock images of people or invented pictures of government events.</p>
                        <p class="public-meta">Albums reserved: {{ implode(', ', $site['gallery_albums']) }}.</p>
                    @endif
                    <a href="{{ route('public.gallery') }}" class="public-btn public-btn-outline" style="margin-top:0.7rem;">Open gallery</a>
                </article>
            </div>
            <div>
                <p class="public-kicker">Office</p>
                <h2>Leadership</h2>
                <article class="public-card">
                    @if ($homeProfiles->isNotEmpty())
                        @php $lead = $homeProfiles->first(); @endphp
                        @if ($lead->photo?->publicUrl())
                            <img src="{{ $lead->photo->publicUrl() }}" alt="" width="320" height="240" style="width:100%;height:8rem;object-fit:cover;border-radius:0.5rem;">
                        @endif
                        <h3>{{ $lead->designation }}</h3>
                        <p>{{ $lead->full_name }}</p>
                    @else
                        <span class="public-draft">Placeholder — name and photograph not confirmed</span>
                        <div class="public-media-ph">Management photograph not yet supplied</div>
                        <h3>{{ $site['management'][0]['designation'] }}</h3>
                        <p>{{ $site['management'][0]['name'] }}</p>
                        <p>{{ $site['management'][0]['biography'] }}</p>
                    @endif
                    <a href="{{ route('public.management') }}" class="public-btn public-btn-outline" style="margin-top:0.7rem;">Management team</a>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="public-section public-navy">
    <div class="public-wrap">
        <h2>Authorised officers</h2>
        <p class="lede">MDA officers and other authorised roles sign in to ODPPMES to record and monitor projects. The public cannot create an account from this website.</p>
        <div class="public-actions">
            <a href="{{ route('login') }}" class="public-btn public-btn-primary">Portal Login</a>
            <a href="{{ route('public.contact') }}" class="public-btn public-btn-hero">Contact the Office</a>
        </div>
    </div>
</section>
@endsection

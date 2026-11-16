@extends('layouts.public')

@section('title', 'About Us — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'About the Development Partners Management Office',
    'lede' => 'The official institutional profile of the Ondo State Development Partners Management Office.',
])

<section class="public-section">
    <div class="public-wrap public-grid public-grid-2">
        <div>
            <p class="public-kicker">Who we are</p>
            <h2>Introduction</h2>
            <div class="public-prose" style="margin-top:0.75rem;">
                @foreach ($site['who_we_are'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
        <figure class="public-card" style="padding:0;overflow:hidden;">
            <img src="{{ asset('images/hero-development.jpg') }}"
                 width="640"
                 height="400"
                 alt="Illustrative photograph of development work. Not a DPMO activity or an Ondo State official event."
                 style="width:100%;height:16rem;object-fit:cover;display:block;">
            <figcaption class="public-meta" style="padding:0.85rem 1.1rem 1.1rem;">
                Illustrative photograph from Unsplash (free licence). It is not a picture of DPMO staff, partners or an official Ondo State event.
            </figcaption>
        </figure>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        <p class="public-kicker">Mandate</p>
        <h2>Institutional mandate</h2>
        <div class="public-grid public-grid-2">
            @foreach ($site['mandate']['paragraphs'] as $paragraph)
                <article class="public-card">
                    <p>{{ $paragraph }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-wrap public-grid public-grid-2">
        <article class="public-card">
            <p class="public-kicker">Direction</p>
            <h3>Vision statement</h3>
            <p>{{ $site['vision']['text'] }}</p>
        </article>
        <article class="public-card">
            <p class="public-kicker">Purpose</p>
            <h3>Mission statement</h3>
            <p>{{ $site['mission']['text'] }}</p>
        </article>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        <p class="public-kicker">What we pursue</p>
        <h2>Core objectives</h2>
        <p class="lede">The Development Partners Management Office shall pursue the following objectives.</p>
        <div class="public-grid public-grid-2">
            @foreach ($site['objectives']['items'] as $index => $item)
                <article class="public-card">
                    <span class="public-num" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">How we work</p>
        <h2>Strategic pillars</h2>
        <p class="lede">{{ $site['strategic_pillars']['intro'] }}</p>
        <div class="public-grid public-grid-3">
            @foreach ($site['strategic_pillars']['items'] as $index => $pillar)
                <article class="public-card">
                    <span class="public-num" aria-hidden="true">{{ $index + 1 }}</span>
                    <h3>{{ $pillar['title'] }}</h3>
                    <p>{{ $pillar['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        @include('public.partials.stakeholder-categories')
        <h3 style="margin-top:1.75rem;color:var(--odp-navy);font-size:1.05rem;font-weight:800;">Examples from the institutional profile</h3>
        <p class="lede">{{ $site['stakeholder_examples']['intro'] }}</p>
        <div class="public-grid public-grid-4">
            @foreach ($site['stakeholder_examples']['items'] as $name)
                <article class="public-card">
                    <span class="public-draft">Example only</span>
                    <h3>{{ $name }}</h3>
                    <p>Named in the institutional profile as a multilateral development institution. Not listed here as a confirmed partner of Ondo State.</p>
                </article>
            @endforeach
        </div>
        <p class="public-note">{{ $site['stakeholder_examples']['disclaimer'] }}</p>
    </div>
</section>
@endsection

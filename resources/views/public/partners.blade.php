@extends('layouts.public')

@section('title', 'Development Partners — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Development Stakeholders and Strategic Partners',
    'lede' => $site['stakeholder_section']['intro'],
])

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">Confirmed for publication</p>
        <h2>Official partners</h2>
        <p class="lede">Named organisations appear here only after the Office confirms them for the public website. Internal ODPPMES partner records are not listed automatically.</p>
        @if ($partners->isNotEmpty())
            <div class="public-grid public-grid-3">
                @foreach ($partners as $partner)
                    <article class="public-card">
                        @if ($partner->logo?->publicUrl())
                            <img src="{{ $partner->logo->publicUrl() }}" alt="" width="160" height="80" style="max-height:4rem;width:auto;margin-bottom:0.6rem;">
                        @endif
                        <h3>{{ $partner->name }}</h3>
                        <p class="public-meta">{{ $partner->category }}</p>
                        <p>{{ $partner->summary }}</p>
                    </article>
                @endforeach
            </div>
        @elseif ($site['confirmed_partners'] === [])
            <p class="public-note">No named official partners have been released for this public website yet. Internal partner records in ODPPMES are not listed here.</p>
        @else
            <div class="public-grid public-grid-3">
                @foreach ($site['confirmed_partners'] as $partner)
                    <article class="public-card">
                        <h3>{{ $partner['name'] }}</h3>
                        <p>{{ $partner['note'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="public-section public-wash">
    <div class="public-wrap">
        @include('public.partials.stakeholder-categories')
    </div>
</section>
@endsection

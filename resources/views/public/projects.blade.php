@extends('layouts.public')

@section('title', 'Projects & Programmes — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Projects and programmes',
    'lede' => 'Public summaries of partner-supported work. Detailed monitoring, documents, photographs uploaded in the portal, and financial records are not published on this website.',
])

<section class="public-section">
    <div class="public-wrap">
        @if ($highlights->isNotEmpty())
            <div class="public-grid public-grid-3">
                @foreach ($highlights as $highlight)
                    <article class="public-card">
                        <h3>{{ $highlight->title }}</h3>
                        <p>{{ $highlight->summary }}</p>
                        <p class="public-meta"><strong>Implementing MDA:</strong> {{ $highlight->implementing_mda_name }}</p>
                        <p class="public-meta"><strong>Development partner:</strong> {{ $highlight->partner_name }}</p>
                        <p class="public-meta"><strong>Sector:</strong> {{ $highlight->sector }}</p>
                        <p class="public-meta"><strong>Location:</strong> {{ $highlight->location }}</p>
                        <p class="public-meta"><strong>Status:</strong> {{ $highlight->public_status }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="public-note">The entries below are editable public drafts. They are not exported from confidential ODPPMES project records.</p>
            <div class="public-grid public-grid-3">
                @foreach ($site['featured_programmes'] as $programme)
                    <article class="public-card">
                        @if (! empty($programme['draft']))
                            <span class="public-draft">Editable draft</span>
                        @endif
                        <h3>{{ $programme['title'] }}</h3>
                        <p>{{ $programme['description'] }}</p>
                        <p class="public-meta"><strong>Implementing MDA:</strong> {{ $programme['mda'] }}</p>
                        <p class="public-meta"><strong>Development partner:</strong> {{ $programme['partner'] }}</p>
                        <p class="public-meta"><strong>Sector:</strong> {{ $programme['sector'] }}</p>
                        <p class="public-meta"><strong>Location:</strong> {{ $programme['location'] }}</p>
                        <p class="public-meta"><strong>Status:</strong> {{ $programme['status'] }}</p>
                        <p class="public-meta">Photograph: none published. Official project photographs will be added only when the Office supplies images cleared for the public website.</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

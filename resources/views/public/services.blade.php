@extends('layouts.public')

@section('title', 'Services — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Services',
    'lede' => 'In accordance with the mandate of the Development Partners Management Office, these functions and responsibilities support development cooperation in Ondo State.',
])

<section class="public-section">
    <div class="public-wrap">
        <p class="public-kicker">Official functions</p>
        <h2>What the Office does</h2>
        <p class="lede">The twelve services below are taken from the official institutional profile. They describe coordination and facilitation work. They do not create new statutory powers or replace the mandates of implementing MDAs.</p>
        <div class="public-grid public-grid-3">
            @foreach ($site['services'] as $index => $service)
                <article class="public-card">
                    <div class="public-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="20" height="20">{!! $service['icon'] !!}</svg>
                    </div>
                    <span class="public-num" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['text'] }}</p>
                </article>
            @endforeach
        </div>
        <p class="public-note">Detailed monitoring records, agreements and confidential project files remain in the authorised ODPPMES portal. Public pages summarise the Office’s role; they do not publish operational files.</p>
    </div>
</section>
@endsection

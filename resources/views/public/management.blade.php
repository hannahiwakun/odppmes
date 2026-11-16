@extends('layouts.public')

@section('title', 'Management Team — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Management team',
    'lede' => 'Leadership profiles show verified names, designations, photographs and biographies supplied by the Office.',
])

<section class="public-section">
    <div class="public-wrap">
        @if ($profiles->isNotEmpty())
            <div class="public-grid public-grid-3">
                @foreach ($profiles as $profile)
                    <article class="public-card">
                        @if ($profile->photo?->publicUrl())
                            <img src="{{ $profile->photo->publicUrl() }}" alt="" width="320" height="240" style="width:100%;height:8.5rem;object-fit:cover;border-radius:0.5rem;margin:0.65rem 0 0.8rem;">
                        @else
                            <div style="height:8.5rem;border-radius:0.5rem;background:var(--odp-wash);border:1px dashed var(--odp-line);display:flex;align-items:center;justify-content:center;color:var(--odp-muted);font-size:0.85rem;margin:0.65rem 0 0.8rem;">
                                Photograph not published
                            </div>
                        @endif
                        <h3>{{ $profile->full_name }}</h3>
                        <p class="public-meta">{{ $profile->designation }}</p>
                        <p>{{ $profile->biography }}</p>
                        @if ($profile->responsibilities)
                            <p><strong>Responsibilities:</strong> {{ $profile->responsibilities }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="public-note">Names, photographs and titles have not been invented. Replace each placeholder when the Office supplies an authorised list.</p>
            <div class="public-grid public-grid-3">
                @foreach ($site['management'] as $profile)
                    <article class="public-card">
                        @if (! empty($profile['placeholder']))
                            <span class="public-draft">Placeholder profile</span>
                        @endif
                        <div style="height:8.5rem;border-radius:0.5rem;background:var(--odp-wash);border:1px dashed var(--odp-line);display:flex;align-items:center;justify-content:center;color:var(--odp-muted);font-size:0.85rem;margin:0.65rem 0 0.8rem;">
                            Photograph not yet supplied
                        </div>
                        <h3>{{ $profile['name'] }}</h3>
                        <p class="public-meta">{{ $profile['designation'] }}</p>
                        <p>{{ $profile['biography'] }}</p>
                        <p><strong>Responsibilities:</strong> {{ $profile['responsibilities'] }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

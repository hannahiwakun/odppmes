@php
    $url = $banner->image?->publicUrl();
    $alt = $banner->imageAltText();
    $class = $class ?? 'public-banner-image';
@endphp
@if ($url)
    <img src="{{ $url }}" alt="{{ $alt }}" width="960" height="540" class="{{ $class }}">
@else
    <div class="public-media-ph {{ $placeholderClass ?? 'public-banner-image-ph' }}" role="img" aria-label="{{ $alt }}">Image not yet available</div>
@endif

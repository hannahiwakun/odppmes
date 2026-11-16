@php
    $url = $banner->safeLinkUrl();
    $label = $banner->callToActionLabel();
    $class = $class ?? 'public-btn public-btn-primary';
@endphp
@if ($url && $label)
    <a href="{{ $url }}" class="{{ $class }}"
       @if ($banner->isExternalLink()) rel="noopener noreferrer" target="_blank" @endif>{{ $label }}</a>
@endif

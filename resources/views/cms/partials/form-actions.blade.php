@php
    $submitLabel = $submitLabel ?? 'Save draft';
    $cancelUrl = $cancelUrl ?? null;
@endphp
<div class="flex flex-col gap-3 border-t border-green-100 pt-4 sm:flex-row">
    <button type="submit" class="cms-btn cms-btn-primary">{{ $submitLabel }}</button>
    @if($cancelUrl)
        <a href="{{ $cancelUrl }}" class="cms-btn cms-btn-secondary">Cancel</a>
    @endif
</div>

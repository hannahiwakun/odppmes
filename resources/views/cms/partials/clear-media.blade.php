@if($asset)
    @if($asset->is_cleared_for_public)
        <p class="mt-2 rounded-md bg-green-50 px-3 py-2 text-sm text-green-900">
            <span class="font-semibold">Cleared for public use.</span>
            Super Administrator has authorised this file to appear on the public website when the related content is published.
        </p>
    @else
        <p class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-950">
            <span class="font-semibold">Not cleared for public use.</span>
            The file is stored privately in the CMS and will not be shown on the public website.
        </p>
        @can('clearForPublic', $asset)
            <form method="POST" action="{{ route('cms.media.clear-for-public', $asset) }}" class="mt-2" onsubmit="return confirm('Allow this image to be shown on the public website?');">
                @csrf
                <button type="submit" class="cms-btn cms-btn-secondary">Clear for public use</button>
            </form>
        @endcan
    @endif
@endif

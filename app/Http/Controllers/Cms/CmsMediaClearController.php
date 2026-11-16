<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMediaAsset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class CmsMediaClearController extends Controller
{
    use AuthorizesRequests;

    public function store(CmsMediaAsset $asset): RedirectResponse
    {
        $this->authorize('clearForPublic', $asset);

        $asset->is_cleared_for_public = true;
        $asset->save();

        return back()->with('success', 'The file is cleared for public use after the related content is published.');
    }
}

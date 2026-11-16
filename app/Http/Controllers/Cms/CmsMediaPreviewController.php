<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMediaAsset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CmsMediaPreviewController extends Controller
{
    use AuthorizesRequests;

    public function show(CmsMediaAsset $asset): StreamedResponse
    {
        $this->authorize('view', $asset);

        abort_unless($asset->disk === CmsMediaAsset::DISK, 404);
        abort_unless(Storage::disk(CmsMediaAsset::DISK)->exists($asset->path), 404);

        $extension = pathinfo($asset->path, PATHINFO_EXTENSION);
        $downloadName = $asset->uuid.'.'.$extension;

        return Storage::disk(CmsMediaAsset::DISK)->response(
            $asset->path,
            $downloadName,
            [
                'Content-Type' => $asset->mime,
                'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
                'Cache-Control' => 'private, max-age=0, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}

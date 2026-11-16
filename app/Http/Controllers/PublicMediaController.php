<?php

namespace App\Http\Controllers;

use App\Cms\CmsMediaUploader;
use App\Models\CmsGalleryItem;
use App\Models\CmsHomepageBanner;
use App\Models\CmsManagementProfile;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\CmsPublicPartner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function show(string $uuid): StreamedResponse
    {
        abort_unless(Str::isUuid($uuid), 404);

        $asset = CmsMediaAsset::query()->where('uuid', $uuid)->first();
        abort_if($asset === null || ! $asset->isPubliclyServable(), 404);
        abort_unless($this->attachedToPublishedContent($asset), 404);

        $disk = Storage::disk(CmsMediaAsset::DISK);
        abort_unless($disk->exists($asset->path), 404);

        $absolute = $disk->path($asset->path);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->file($absolute) ?: '';
        abort_unless(isset(CmsMediaUploader::MIME_EXTENSIONS[$detected]), 404);

        $info = @getimagesize($absolute);
        abort_if($info === false || ! in_array($info[2], CmsMediaUploader::IMAGE_TYPES, true), 404);

        $head = strtolower(substr((string) file_get_contents($absolute, false, null, 0, 256), 0, 256));
        foreach (['<svg', '<html', '<?php', '<script'] as $needle) {
            abort_if(str_contains($head, $needle), 404);
        }

        $extension = CmsMediaUploader::MIME_EXTENSIONS[$detected];
        $downloadName = $asset->uuid.'.'.$extension;

        return $disk->response($asset->path, $downloadName, [
            'Content-Type' => $detected,
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function attachedToPublishedContent(CmsMediaAsset $asset): bool
    {
        $gallery = CmsGalleryItem::query()
            ->published()
            ->where('media_id', $asset->id)
            ->whereHas('album', fn ($query) => $query->published())
            ->exists();

        if ($gallery) {
            return true;
        }

        if (CmsManagementProfile::query()->published()->where('photo_media_id', $asset->id)->exists()) {
            return true;
        }

        if (CmsPublicPartner::query()->published()->where('logo_media_id', $asset->id)->exists()) {
            return true;
        }

        if (CmsHomepageBanner::query()->published()->where('image_media_id', $asset->id)->exists()) {
            return true;
        }

        return CmsNewsArticle::query()->published()->where('featured_image_media_id', $asset->id)->exists();
    }
}

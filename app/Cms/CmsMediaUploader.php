<?php

namespace App\Cms;

use App\Models\CmsMediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CmsMediaUploader
{
    public const DISK = 'local';

    public const MAX_BYTES = 5242880;

    /**
     * @var array<string, string>
     */
    public const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @var list<int>
     */
    public const IMAGE_TYPES = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_WEBP,
    ];

    public function store(UploadedFile $file, User $uploader): CmsMediaAsset
    {
        $this->assertSafeUpload($file);

        $mime = $this->detectedMime($file);
        $extension = self::MIME_EXTENSIONS[$mime];
        $uuid = (string) Str::uuid();
        $relative = 'cms/media/'.now()->format('Y/m').'/'.$uuid.'.'.$extension;

        $binary = $this->reencode($file->getRealPath(), $mime);
        Storage::disk(self::DISK)->put($relative, $binary);

        $dimensions = @getimagesizefromstring($binary) ?: [null, null];

        $asset = new CmsMediaAsset;
        $asset->uuid = $uuid;
        $asset->disk = self::DISK;
        $asset->path = $relative;
        $asset->original_name = $file->getClientOriginalName();
        $asset->mime = $mime;
        $asset->size_bytes = strlen($binary);
        $asset->width = is_int($dimensions[0] ?? null) ? $dimensions[0] : null;
        $asset->height = is_int($dimensions[1] ?? null) ? $dimensions[1] : null;
        $asset->checksum_sha256 = hash('sha256', $binary);
        $asset->uploaded_by = $uploader->id;
        $asset->is_cleared_for_public = false;
        $asset->save();

        return $asset;
    }

    private function assertSafeUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'image' => 'The uploaded file could not be read.',
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'image' => 'Images must be 5 MB or smaller.',
            ]);
        }

        $path = $file->getRealPath();
        $mime = $this->detectedMime($file);
        $info = @getimagesize($path);

        if ($info === false || ! in_array($info[2], self::IMAGE_TYPES, true) || ! isset(self::MIME_EXTENSIONS[$mime])) {
            throw ValidationException::withMessages([
                'image' => 'Only JPEG, PNG and WebP images are allowed.',
            ]);
        }

        $head = strtolower(substr((string) file_get_contents($path, false, null, 0, 256), 0, 256));
        foreach (['<svg', '<html', '<?php', '<script', '<?xml'] as $needle) {
            if (str_contains($head, $needle)) {
                throw ValidationException::withMessages([
                    'image' => 'This file type is not allowed.',
                ]);
            }
        }
    }

    private function detectedMime(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file->getRealPath()) ?: '';

        return $mime;
    }

    private function reencode(string $path, string $mime): string
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($image === false) {
            $raw = file_get_contents($path);
            if ($raw === false) {
                throw ValidationException::withMessages([
                    'image' => 'The uploaded file could not be processed.',
                ]);
            }

            return $raw;
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        ob_start();
        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, null, 90),
            'image/png' => imagepng($image, null, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, null, 90) : false,
            default => false,
        };
        imagedestroy($image);
        $binary = ob_get_clean();

        if ($ok === false || $binary === false || $binary === '') {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be re-encoded.',
            ]);
        }

        return $binary;
    }
}

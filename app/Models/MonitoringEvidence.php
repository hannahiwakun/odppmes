<?php

namespace App\Models;

use App\Support\OperationalStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringEvidence extends Model
{
    public const DIRECTORY = 'monitorings/evidence';

    public const TYPE_PHOTO = 'photo';

    public const TYPE_VIDEO = 'video';

    public const PHOTO_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public const VIDEO_MIME_TYPES = [
        'video/mp4' => 'mp4',
    ];

    public const PHOTO_MAX_KILOBYTES = 5120;

    public const VIDEO_MAX_KILOBYTES = 102400;

    protected $fillable = [
        'monitoring_id',
        'file_path',
        'file_type',
        'original_filename',
        'mime_type',
        'file_size',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(Monitoring::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isPhoto(): bool
    {
        return $this->file_type === self::TYPE_PHOTO;
    }

    public function isVideo(): bool
    {
        return $this->file_type === self::TYPE_VIDEO;
    }

    public static function isSafePath(?string $path): bool
    {
        $path = str_replace('\\', '/', (string) $path);

        return $path !== ''
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0")
            && str_starts_with($path, self::DIRECTORY.'/')
            && substr_count($path, '/') === 2;
    }

    public static function deleteStoredFile(?string $path): void
    {
        if (! self::isSafePath($path)) {
            return;
        }

        $disk = OperationalStorage::disk();

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

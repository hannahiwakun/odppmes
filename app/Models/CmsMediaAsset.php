<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsMediaAsset extends Model
{
    use SoftDeletes;

    public const DISK = 'local';

    protected $fillable = [
        'original_name',
        'mime',
        'size_bytes',
        'width',
        'height',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_cleared_for_public' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(CmsGalleryItem::class, 'media_id');
    }

    public function isPubliclyServable(): bool
    {
        return $this->is_cleared_for_public === true
            && $this->disk === self::DISK
            && $this->deleted_at === null;
    }

    public function publicUrl(): ?string
    {
        if (! $this->isPubliclyServable()) {
            return null;
        }

        return route('public.media', $this->uuid);
    }
}

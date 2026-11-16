<?php

namespace App\Models;

use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsGalleryAlbum extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CmsGalleryItem::class, 'album_id')->orderBy('sort_order');
    }

    public function publicItems(): HasMany
    {
        return $this->hasMany(CmsGalleryItem::class, 'album_id')
            ->published()
            ->orderBy('sort_order')
            ->whereHas('media', function ($query) {
                $query->where('is_cleared_for_public', true)
                    ->where('disk', CmsMediaAsset::DISK);
            });
    }
}

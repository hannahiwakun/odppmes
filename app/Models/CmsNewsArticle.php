<?php

namespace App\Models;

use App\Cms\CmsNewsCategories;
use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsNewsArticle extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'featured_image_alt',
        'category',
        'published_from',
        'published_until',
    ];

    protected function casts(): array
    {
        return [
            'published_from' => 'datetime',
            'published_until' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(CmsMediaAsset::class, 'featured_image_media_id');
    }

    public function categoryLabel(): string
    {
        return CmsNewsCategories::display($this->category);
    }
}

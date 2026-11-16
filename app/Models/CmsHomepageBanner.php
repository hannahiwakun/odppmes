<?php

namespace App\Models;

use App\Cms\CmsHomepageBannerTypes;
use App\Cms\CmsSafeHttpUrl;
use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsHomepageBanner extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    public const TYPE_HERO_NOTICE = 'hero_notice';

    public const TYPE_ALERT = 'alert';

    public const TYPE_FEATURE = 'feature';

    protected $fillable = [
        'title',
        'body',
        'banner_type',
        'image_alt',
        'link_url',
        'button_text',
        'sort_order',
        'published_from',
        'published_until',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_from' => 'datetime',
            'published_until' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(CmsMediaAsset::class, 'image_media_id');
    }

    public function typeLabel(): string
    {
        return CmsHomepageBannerTypes::label($this->banner_type);
    }

    public function safeLinkUrl(): ?string
    {
        return CmsSafeHttpUrl::sanitize($this->link_url);
    }

    public function isExternalLink(): bool
    {
        return CmsSafeHttpUrl::isExternal($this->link_url);
    }

    public function callToActionLabel(): ?string
    {
        if ($this->safeLinkUrl() === null) {
            return null;
        }

        $label = trim((string) $this->button_text);

        return $label !== '' ? $label : 'Read more';
    }

    public function imageAltText(): string
    {
        $alt = trim((string) $this->image_alt);

        return $alt !== '' ? $alt : (string) $this->title;
    }
}

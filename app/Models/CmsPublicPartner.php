<?php

namespace App\Models;

use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPublicPartner extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'summary',
        'logo_media_id',
        'website_url',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(CmsMediaAsset::class, 'logo_media_id');
    }
}

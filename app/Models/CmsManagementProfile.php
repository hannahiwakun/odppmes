<?php

namespace App\Models;

use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsManagementProfile extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'full_name',
        'designation',
        'biography',
        'responsibilities',
        'photo_media_id',
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

    public function photo(): BelongsTo
    {
        return $this->belongsTo(CmsMediaAsset::class, 'photo_media_id');
    }
}

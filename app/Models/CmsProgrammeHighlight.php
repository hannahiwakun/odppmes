<?php

namespace App\Models;

use App\Contracts\CmsWorkflowContent;
use App\Models\Concerns\HasCmsWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsProgrammeHighlight extends Model implements CmsWorkflowContent
{
    use HasCmsWorkflow;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'implementing_mda_name',
        'partner_name',
        'sector',
        'location',
        'public_status',
        'summary',
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
}

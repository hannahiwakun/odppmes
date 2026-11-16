<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsEnquiry extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_SPAM = 'spam';

    protected $hidden = [
        'email',
        'name',
        'organisation',
        'message',
        'source_ip',
        'source_file',
        'source_hash',
        'internal_notes',
    ];

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

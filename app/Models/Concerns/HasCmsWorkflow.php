<?php

namespace App\Models\Concerns;

use App\Cms\CmsWorkflowStatus;
use App\Models\CmsWorkflowEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCmsWorkflow
{
    public const STATUS_DRAFT = CmsWorkflowStatus::DRAFT;

    public const STATUS_SUBMITTED = CmsWorkflowStatus::SUBMITTED;

    public const STATUS_REJECTED = CmsWorkflowStatus::REJECTED;

    public const STATUS_APPROVED = CmsWorkflowStatus::APPROVED;

    public const STATUS_PUBLISHED = CmsWorkflowStatus::PUBLISHED;

    public const STATUS_ARCHIVED = CmsWorkflowStatus::ARCHIVED;

    /**
     * Workflow and actor fields that must never be mass assigned.
     *
     * @return list<string>
     */
    public static function protectedWorkflowAttributes(): array
    {
        return [
            'status',
            'created_by',
            'updated_by',
            'submitted_by',
            'submitted_at',
            'reviewed_by',
            'reviewed_at',
            'approved_by',
            'rejected_reason',
            'published_by',
            'published_at',
            'archived_by',
            'archived_at',
        ];
    }

    /**
     * @return list<string>
     */
    public static function workflowStatuses(): array
    {
        return CmsWorkflowStatus::all();
    }

    protected static function bootHasCmsWorkflow(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('status') === null || $model->getAttribute('status') === '') {
                $model->setAttribute('status', CmsWorkflowStatus::DRAFT);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function workflowEvents(): MorphMany
    {
        return $this->morphMany(CmsWorkflowEvent::class, 'content')->latest('id');
    }

    public function isPublished(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        $now = now();

        if ($this->published_at !== null && $this->published_at->isFuture()) {
            return false;
        }

        if (isset($this->published_from) && $this->published_from !== null && $this->published_from->isFuture()) {
            return false;
        }

        if (isset($this->published_until) && $this->published_until !== null && $this->published_until->lte($now)) {
            return false;
        }

        return true;
    }

    public function scopePublished(Builder $query): Builder
    {
        $query->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $inner) {
                $inner->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        $model = $query->getModel();
        $casts = $model->getCasts();

        if (array_key_exists('published_from', $casts)) {
            $query->where(function (Builder $inner) {
                $inner->whereNull('published_from')
                    ->orWhere('published_from', '<=', now());
            });
        }

        if (array_key_exists('published_until', $casts)) {
            $query->where(function (Builder $inner) {
                $inner->whereNull('published_until')
                    ->orWhere('published_until', '>', now());
            });
        }

        return $query;
    }
}

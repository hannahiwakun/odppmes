<?php

namespace App\Cms;

use App\Models\CmsWorkflowEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CmsWorkflowManager
{
    public function submit(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'submit', CmsWorkflowStatus::SUBMITTED, [
            'submitted_by' => $actor->id,
            'submitted_at' => now(),
            'rejected_reason' => null,
        ]);
    }

    public function approve(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'approve', CmsWorkflowStatus::APPROVED, [
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
            'rejected_reason' => null,
        ]);
    }

    public function reject(Model $content, User $actor, string $reason): Model
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'A rejection reason is required.',
            ]);
        }

        return $this->transition($content, $actor, 'reject', CmsWorkflowStatus::REJECTED, [
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
            'rejected_reason' => $reason,
        ], $reason);
    }

    public function publish(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'publish', CmsWorkflowStatus::PUBLISHED, [
            'published_by' => $actor->id,
            'published_at' => now(),
        ]);
    }

    public function unpublish(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'unpublish', CmsWorkflowStatus::APPROVED, []);
    }

    public function archive(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'archive', CmsWorkflowStatus::ARCHIVED, [
            'archived_by' => $actor->id,
            'archived_at' => now(),
        ]);
    }

    public function restore(Model $content, User $actor): Model
    {
        return $this->transition($content, $actor, 'restore', CmsWorkflowStatus::APPROVED, []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transition(
        Model $content,
        User $actor,
        string $ability,
        string $toStatus,
        array $attributes,
        ?string $comment = null,
    ): Model {
        Gate::forUser($actor)->authorize($ability, $content);

        $fromStatus = (string) $content->status;
        $allowedFrom = $this->allowedFromStatuses($ability);

        if (! in_array($fromStatus, $allowedFrom, true)) {
            throw ValidationException::withMessages([
                'status' => 'This workflow action is not available for the current content status.',
            ]);
        }

        return DB::transaction(function () use ($content, $actor, $fromStatus, $toStatus, $attributes, $comment) {
            foreach ($attributes as $key => $value) {
                $content->{$key} = $value;
            }

            $content->status = $toStatus;
            $content->updated_by = $actor->id;
            $content->save();

            $event = new CmsWorkflowEvent;
            $event->content()->associate($content);
            $event->from_status = $fromStatus;
            $event->to_status = $toStatus;
            $event->actor_id = $actor->id;
            $event->comment = $comment;
            $event->save();

            return $content->refresh();
        });
    }

    /**
     * @return list<string>
     */
    private function allowedFromStatuses(string $ability): array
    {
        return match ($ability) {
            'submit' => [CmsWorkflowStatus::DRAFT, CmsWorkflowStatus::REJECTED],
            'approve', 'reject' => [CmsWorkflowStatus::SUBMITTED],
            'publish' => [CmsWorkflowStatus::APPROVED],
            'unpublish' => [CmsWorkflowStatus::PUBLISHED],
            'archive' => [CmsWorkflowStatus::APPROVED, CmsWorkflowStatus::PUBLISHED],
            'restore' => [CmsWorkflowStatus::ARCHIVED],
            default => [],
        };
    }
}

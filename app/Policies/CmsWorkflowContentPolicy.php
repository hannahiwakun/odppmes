<?php

namespace App\Policies;

use App\Cms\CmsPermissions;
use App\Cms\CmsWorkflowStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CmsWorkflowContentPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function view(User $user, Model $content): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function create(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function update(User $user, Model $content): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return CmsPermissions::websiteAdminMayEdit($user, $content);
    }

    public function delete(User $user, Model $content): bool
    {
        return $user->isSuperAdmin();
    }

    public function submit(User $user, Model $content): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return CmsPermissions::websiteAdminMayEdit($user, $content);
    }

    public function approve(User $user, Model $content): bool
    {
        return CmsPermissions::canReview($user);
    }

    public function reject(User $user, Model $content): bool
    {
        return CmsPermissions::canReview($user);
    }

    public function publish(User $user, Model $content): bool
    {
        return CmsPermissions::canPublish($user);
    }

    public function unpublish(User $user, Model $content): bool
    {
        return CmsPermissions::canPublish($user);
    }

    public function archive(User $user, Model $content): bool
    {
        return CmsPermissions::canArchive($user);
    }

    public function restore(User $user, Model $content): bool
    {
        return CmsPermissions::canArchive($user);
    }

    public function viewHistory(User $user, Model $content): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isWebsiteAdmin()) {
            return false;
        }

        return CmsPermissions::owns($user, $content)
            || in_array((string) $content->status, [
                CmsWorkflowStatus::DRAFT,
                CmsWorkflowStatus::SUBMITTED,
                CmsWorkflowStatus::REJECTED,
            ], true);
    }
}

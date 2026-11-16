<?php

namespace App\Policies;

use App\Cms\CmsPermissions;
use App\Models\CmsWorkflowEvent;
use App\Models\User;

class CmsWorkflowEventPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function view(User $user, CmsWorkflowEvent $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! CmsPermissions::canAccessCms($user) || $event->content === null) {
            return false;
        }

        return $user->can('viewHistory', $event->content);
    }
}

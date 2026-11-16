<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($user->isWebsiteAdmin()) {
            return false;
        }

        return $user->canAccessProject($project);
    }

    public function create(User $user): bool
    {
        if ($user->isWebsiteAdmin() || $user->isViewer() || $user->isDevelopmentPartner()) {
            return false;
        }

        if ($user->isMdaOfficer()) {
            return $user->mda_id !== null;
        }

        return $user->isSuperAdmin() || $user->isMEO();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isWebsiteAdmin() || $user->isViewer() || $user->isDevelopmentPartner()) {
            return false;
        }

        return $user->canAccessProject($project);
    }

    /**
     * Programme administrators may delete a project.
     * MDA Officers can work on projects but cannot remove them.
     */
    public function delete(User $user, Project $project): bool
    {
        return ! $user->isWebsiteAdmin() && ($user->isSuperAdmin() || $user->isMEO());
    }

    public function updateVerification(User $user, Project $project): bool
    {
        return ! $user->isWebsiteAdmin() && ($user->isSuperAdmin() || $user->isMEO());
    }
}

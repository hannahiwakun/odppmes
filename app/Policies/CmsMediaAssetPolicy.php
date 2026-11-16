<?php

namespace App\Policies;

use App\Cms\CmsPermissions;
use App\Models\CmsMediaAsset;
use App\Models\User;

class CmsMediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function view(User $user, CmsMediaAsset $asset): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function create(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function update(User $user, CmsMediaAsset $asset): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isWebsiteAdmin()
            && (int) $asset->uploaded_by === (int) $user->id;
    }

    public function delete(User $user, CmsMediaAsset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function clearForPublic(User $user, CmsMediaAsset $asset): bool
    {
        return $user->isSuperAdmin();
    }
}

<?php

namespace App\Policies;

use App\Cms\CmsPermissions;
use App\Models\CmsEnquiry;
use App\Models\User;

class CmsEnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function view(User $user, CmsEnquiry $enquiry): bool
    {
        return CmsPermissions::canAccessCms($user);
    }

    public function update(User $user, CmsEnquiry $enquiry): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, CmsEnquiry $enquiry): bool
    {
        return $user->isSuperAdmin();
    }
}

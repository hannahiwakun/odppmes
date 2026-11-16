<?php

namespace App\Cms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class CmsPermissions
{
    /**
     * @return list<string>
     */
    public static function websiteAdminEditableStatuses(): array
    {
        return ['draft', 'rejected'];
    }

    public static function canAccessCms(?User $user): bool
    {
        return $user !== null && $user->hasCmsAccess();
    }

    public static function canReview(?User $user): bool
    {
        return $user !== null && $user->isSuperAdmin();
    }

    public static function canPublish(?User $user): bool
    {
        return $user !== null && $user->isSuperAdmin();
    }

    public static function canArchive(?User $user): bool
    {
        return $user !== null && $user->isSuperAdmin();
    }

    public static function canManageUsers(?User $user): bool
    {
        return $user !== null && $user->isSuperAdmin();
    }

    public static function owns(User $user, Model $content): bool
    {
        if (! isset($content->created_by) || $content->created_by === null) {
            return false;
        }

        return (int) $content->created_by === (int) $user->id;
    }

    public static function websiteAdminMayEdit(User $user, Model $content): bool
    {
        if (! $user->isWebsiteAdmin()) {
            return false;
        }

        if (! self::owns($user, $content)) {
            return false;
        }

        $status = (string) ($content->status ?? '');

        return in_array($status, self::websiteAdminEditableStatuses(), true);
    }
}

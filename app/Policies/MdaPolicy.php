<?php

namespace App\Policies;

use App\Models\Mda;
use App\Models\User;

class MdaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isMEO();
    }

    public function view(User $user, Mda $mda): bool
    {
        return $user->isSuperAdmin() || $user->isMEO();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Mda $mda): bool
    {
        return $user->isSuperAdmin();
    }

    public function deactivate(User $user, Mda $mda): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Mda $mda): bool
    {
        return $user->isSuperAdmin();
    }
}

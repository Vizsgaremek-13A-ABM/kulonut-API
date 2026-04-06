<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Rbac;

class UserPolicy
{
    public function viewAny(?User $user): bool
    {
        return Rbac::isAdmin($user);
    }

    public function view(?User $user, User $model): bool
    {
        return Rbac::isAdmin($user);
    }

    public function create(?User $user): bool
    {
        return Rbac::isAdmin($user);
    }

    public function update(?User $user, User $model): bool
    {
        return Rbac::isAdmin($user);
    }

    public function delete(?User $user, User $model): bool
    {
        return Rbac::isAdmin($user);
    }

    public function uploadProfileIcon(?User $user, User $model): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->id === $model->id) {
            return true;
        }

        return Rbac::isAdmin($user);
    }
}
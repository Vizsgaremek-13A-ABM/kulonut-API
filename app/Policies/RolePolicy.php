<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Support\Rbac;

class RolePolicy
{
    public function viewAny(?User $user): bool
    {
        return Rbac::isAdmin($user);
    }

    public function view(?User $user, Role $role): bool
    {
        return Rbac::isAdmin($user);
    }

    public function create(?User $user): bool
    {
        return Rbac::isAdmin($user);
    }

    public function update(?User $user, Role $role): bool
    {
        return Rbac::isAdmin($user);
    }

    public function delete(?User $user, Role $role): bool
    {
        return Rbac::isAdmin($user);
    }
}
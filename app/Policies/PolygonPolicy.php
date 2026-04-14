<?php

namespace App\Policies;

use App\Models\Polygon;
use App\Models\User;
use App\Support\Rbac;

class PolygonPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Polygon $polygon): bool
    {
        $userLevel = Rbac::levelOf($user);

        $polygon->loadMissing('projects');

        return $polygon->projects->contains(function ($project) use ($userLevel): bool {
            return $userLevel >= (int) ($project->min_role_level ?? 1);
        });
    }

    public function create(?User $user): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }

    public function update(?User $user, Polygon $polygon): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }

    public function delete(?User $user, Polygon $polygon): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }
}
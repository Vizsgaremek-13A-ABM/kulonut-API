<?php

namespace App\Policies;

use App\Models\Coord;
use App\Models\User;
use App\Support\Rbac;

class CoordPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Coord $coord): bool
    {
        $userLevel = Rbac::levelOf($user);

        $coord->loadMissing('polygon.projects');

        return $coord->polygon?->projects?->contains(function ($project) use ($userLevel): bool {
            return $userLevel >= (int) ($project->min_role_level ?? 1);
        }) ?? false;
    }

    public function create(?User $user): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::editorLevel());
    }

    public function update(?User $user, Coord $coord): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::editorLevel());
    }

    public function delete(?User $user, Coord $coord): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::editorLevel());
    }
}
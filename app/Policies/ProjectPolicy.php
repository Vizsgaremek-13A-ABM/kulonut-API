<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;

class ProjectPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Project $project): bool
    {
        return Rbac::levelOf($user) >= $this->projectMinLevel($project);
    }

    public function create(?User $user): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }

    public function update(?User $user, Project $project): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }

    public function delete(?User $user, Project $project): bool
    {
        return Rbac::hasMinimumLevel($user, Rbac::employeeLevel());
    }

    private function projectMinLevel(Project $project): int
    {
        return (int) ($project->min_role_level ?? (int) config('rbac.user_level', 1));
    }
}

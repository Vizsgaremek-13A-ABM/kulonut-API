<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

class Rbac
{

    public static function userLevel(): int
    {
        return Role::userLevel();
    }

    public static function privilegedUserLevel(): int
    {
        return Role::privilegedUserLevel();
    }

    public static function trustedUserLevel(): int
    {
        return Role::trustedUserLevel();
    }

    public static function employeeLevel(): int
    {
        return Role::employeeLevel();
    }

    public static function adminLevel(): int
    {
        return Role::adminLevel();
    }

    public static function levelOf(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $user->loadMissing('role');

        return (int) ($user->role?->level ?? 0);
    }

    public static function hasMinimumLevel(?User $user, int $minimumLevel): bool
    {
        return self::levelOf($user) >= $minimumLevel;
    }

    public static function isAdmin(?User $user): bool
    {
        return self::hasMinimumLevel($user, self::adminLevel());
    }
}
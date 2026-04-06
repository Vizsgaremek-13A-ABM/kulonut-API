<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

class Rbac
{
    public static function guestLevel(): int
    {
        return 0;
    }

    public static function userLevel(): int
    {
        return Role::userLevel();
    }

    public static function editorLevel(): int
    {
        return Role::editorLevel();
    }

    public static function adminLevel(): int
    {
        return Role::adminLevel();
    }

    public static function levelOf(?User $user): int
    {
        if (! $user) {
            return self::guestLevel();
        }

        $user->loadMissing('role');

        return (int) ($user->role?->level ?? self::guestLevel());
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    public const USER = 'Felhasználó';
    public const PRIVILEGED_USER = 'Kiemelt felhasználó';
    public const TRUSTED_USER = 'Bizalmi felhasználó';
    public const EMPLOYEE = 'Alkalmazott';
    public const ADMIN = 'Admin';

    protected $fillable = ['role_name', 'description', 'level'];

    protected $casts = [
        'level' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function levelFor(string $roleName, int $defaultLevel = 0): int
    {
        return (int) (static::query()->where('role_name', $roleName)->value('level') ?? $defaultLevel);
    }

    public static function roleIdFor(string $roleName): ?int
    {
        $roleId = static::query()->where('role_name', $roleName)->value('id');

        return $roleId !== null ? (int) $roleId : null;
    }

    public static function userLevel(): int
    {
        return static::levelFor(self::USER, 1);
    }

    public static function privilegedUserLevel(): int
    {
        return static::levelFor(self::PRIVILEGED_USER, 10);
    }

    public static function trustedUserLevel(): int
    {
        return static::levelFor(self::TRUSTED_USER, 25);
    }

    public static function employeeLevel(): int
    {
        return static::levelFor(self::EMPLOYEE, 50);
    }

    public static function adminLevel(): int
    {
        return static::levelFor(self::ADMIN, 99);
    }
}

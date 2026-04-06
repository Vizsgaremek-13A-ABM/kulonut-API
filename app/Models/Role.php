<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    public const USER = 'Felhasználó';
    public const EDITOR = 'Adatszerkesztő';
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
        return static::levelFor(self::USER, 10);
    }

    public static function editorLevel(): int
    {
        return static::levelFor(self::EDITOR, 50);
    }

    public static function adminLevel(): int
    {
        return static::levelFor(self::ADMIN, 99);
    }
}

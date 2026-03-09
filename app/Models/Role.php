<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['role_name', 'description', 'level'];

    protected $casts = [
        'level' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

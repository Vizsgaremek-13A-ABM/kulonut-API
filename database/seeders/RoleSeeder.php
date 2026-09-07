<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'role_name' => "Admin",
            'description' => "Adminisztrátor",
            'level' => 99,
        ]);
        Role::create([
            'role_name' => "Alkalmazott",
            'description' => "Alkalmazott",
            'level' => 50,
        ]);
        Role::create([
            'role_name' => "Bizalmi Felhasználó",
            'description' => "Bizalmi Felhasználó",
            'level' => 25,
        ]);
        Role::create([
            'role_name' => "Kiemelt Felhasználó",
            'description' => "Kiemelt Felhasználó",
            'level' => 10,
        ]);
        Role::create([
            'role_name' => "Felhasználó",
            'description' => "Felhasználó",
            'level' => 1,
        ]);
    }
}

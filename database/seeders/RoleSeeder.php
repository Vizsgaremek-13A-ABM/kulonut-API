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
        for ($index = 1; $index <= 10; $index++) {
            Role::updateOrCreate(['id' => $index], [
                'role_name' => "Role {$index}",
                'description' => "Seeded role {$index}",
                'level' => $index,
            ]);
        }
    }
}

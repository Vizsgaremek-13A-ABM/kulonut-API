<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleIds = Role::query()->pluck('id')->all();

        for ($index = 1; $index <= 10; $index++) {
            User::create([
                'name' => "User {$index}",
                'display_name' => "Display User {$index}",
                'email' => "user{$index}@example.com",
                'password' => Hash::make('password'),
                'role_id' => $roleIds[array_rand($roleIds)],
                'email_verified_at' => now(),
            ]);
        }
    }
}

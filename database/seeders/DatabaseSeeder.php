<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DesignerSeeder::class,
            GeneralDesignerSeeder::class,
            ClientSeeder::class,
            GeodesySeeder::class,
            PolygonSeeder::class,
            ProjectSeeder::class,
            CoordSeeder::class,
            PolygonProjectSeeder::class,
            UserSeeder::class,
        ]);
    }
}

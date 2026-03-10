<?php

namespace Database\Seeders;

use App\Models\Coord;
use App\Models\Polygon;
use Illuminate\Database\Seeder;

class CoordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polygonIds = Polygon::query()->pluck('id')->all();

        for ($index = 1; $index <= 10; $index++) {
            Coord::create([
                'polygon_id' => $polygonIds[array_rand($polygonIds)],
                'latitude' => fake()->randomFloat(2, -89.99, 89.99),
                'longitude' => fake()->randomFloat(2, -179.99, 179.99),
            ]);
        }
    }
}

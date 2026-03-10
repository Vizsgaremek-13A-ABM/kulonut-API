<?php

namespace Database\Seeders;

use App\Models\Polygon;
use Illuminate\Database\Seeder;

class PolygonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($index = 1; $index <= 10; $index++) {
            Polygon::create([
                'name' => "Polygon {$index}",
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Geodesy;
use Illuminate\Database\Seeder;

class GeodesySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Geodesy::create([
                'name' => "Geodesy data {$i}",
            ]);
        }
    }
}

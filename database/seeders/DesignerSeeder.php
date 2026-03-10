<?php

namespace Database\Seeders;

use App\Models\Designer;
use Illuminate\Database\Seeder;

class DesignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($index = 1; $index <= 10; $index++) {
            Designer::create([
                'name' => "Designer {$index}",
            ]);
        }
    }
}

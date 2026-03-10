<?php

namespace Database\Seeders;

use App\Models\GeneralDesigner;
use Illuminate\Database\Seeder;

class GeneralDesignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($index = 1; $index <= 10; $index++) {
            GeneralDesigner::create([
                'name' => "General Designer {$index}",
            ]);
        }
    }
}

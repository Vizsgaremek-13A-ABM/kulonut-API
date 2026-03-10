<?php

namespace Database\Seeders;

use App\Models\Polygon;
use App\Models\Project;
use Illuminate\Database\Seeder;

class PolygonProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polygonIds = Polygon::query()->pluck('id')->all();

        Project::query()->each(function (Project $project) use ($polygonIds) {
            $selectedPolygons = collect($polygonIds)->shuffle()->take(2)->all();
            $project->polygons()->attach($selectedPolygons);
        });
    }
}

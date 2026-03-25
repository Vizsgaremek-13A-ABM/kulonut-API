<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Designer;
use App\Models\Geodesy;
use App\Models\GeneralDesigner;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientIds = Client::query()->pluck('id')->all();
        $designerIds = Designer::query()->pluck('id')->all();
        $generalDesignerIds = GeneralDesigner::query()->pluck('id')->all();
        $geodesyIds = Geodesy::query()->pluck('id')->all();

        if (empty($clientIds) || empty($generalDesignerIds)) {
            // Can't create projects without mandatory relations
            return;
        }

        for ($index = 1; $index <= 10; $index++) {
            Project::create([
                'project_name' => "Project {$index}",
                'work_number' => sprintf('WN-%03d', $index),
                'folder_number' => sprintf('FN-%03d', $index),

                'client_id' => $clientIds[array_rand($clientIds)],
                'general_designer_id' => $generalDesignerIds[array_rand($generalDesignerIds)],
                'designer_id' => empty($designerIds) || random_int(0, 2) === 0 ? null : $designerIds[array_rand($designerIds)],
                'geodesy_id' => empty($geodesyIds) || random_int(0, 5) === 0 ? null : $geodesyIds[array_rand($geodesyIds)],

                'plan_issue_date' => now()->subDays($index),
                'utility_statement_issue_date' => now()->subDays($index + 3),
                'road_construction_permit_date' => now()->subDays($index + 5),
                'water_rights_permit_date' => now()->subDays($index + 7),

                'road_construction_plan' => (bool)random_int(0, 1),
                'water_network_plan' => (bool)random_int(0, 1),
                'sewage_plan' => (bool)random_int(0, 1),
                'stormwater_drainage_plan' => (bool)random_int(0, 1),
                'public_lighting_plan' => (bool)random_int(0, 1),

                'other_work_parts' => "Other work parts {$index}",
                'notes' => "Seed note {$index}",

                'min_role_level' => random_int(1, 10),
            ]);
        }
    }
}

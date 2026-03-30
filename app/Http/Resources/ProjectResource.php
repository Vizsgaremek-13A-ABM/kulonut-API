<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_name' => $this->project_name,
            'work_number' => $this->work_number,
            'folder_number' => $this->folder_number,

            'designer' => $this->relationLoaded('designer') ? $this->designer?->name : null,
            'general_designer' => $this->relationLoaded('generalDesigner') ? $this->generalDesigner?->name : null,
            'client' => $this->relationLoaded('client') ? $this->client?->name : null,
            'geodesy' => $this->relationLoaded('geodesy') ? $this->geodesy?->name : null,

            'plan_issue_date' => $this->plan_issue_date?->format('Y-m-d'),
            'utility_statement_issue_date' => $this->utility_statement_issue_date?->format('Y-m-d'),
            'road_construction_permit_date' => $this->road_construction_permit_date?->format('Y-m-d'),
            'water_rights_permit_date' => $this->water_rights_permit_date?->format('Y-m-d'),

            'road_construction_plan' => $this->road_construction_plan,
            'water_network_plan' => $this->water_network_plan,
            'sewage_plan' => $this->sewage_plan,
            'stormwater_drainage_plan' => $this->stormwater_drainage_plan,
            'public_lighting_plan' => $this->public_lighting_plan,

            'other_work_parts' => $this->other_work_parts,
            'notes' => $this->notes,
            'min_role_level' => $this->min_role_level,
        ];
    }
}

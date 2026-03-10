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

            'client' => $this->client,

            'plan_issue_date' => $this->plan_issue_date?->format('Y-m-d'),
            'eutility_statement_issue_date' => $this->eutility_statement_issue_date?->format('Y-m-d'),
            'road_construction_permit_date' => $this->road_construction_permit_date?->format('Y-m-d'),
            'water_rights_permit_date' => $this->water_rights_permit_date?->format('Y-m-d'),

            'geodesy' => $this->geodesy,
            'road_construction_plan' => $this->road_construction_plan,
            'water_network_plan' => $this->water_network_plan,
            'sewage_plan' => $this->sewage_plan,
            'stormwater_drainage_plan' => $this->stormwater_drainage_plan,
            'public_lighting_plan' => $this->public_lighting_plan,
            'other_work_parts' => $this->other_work_parts,

            'notes' => $this->notes,
            'min_role_level' => $this->min_role_level,

            'designer' => new DesignerResource($this->whenLoaded('designer')),
            'general_designer' => new GeneralDesignerResource($this->whenLoaded('generalDesigner')),

            'polygons' => PolygonResource::collection($this->whenLoaded('polygons')),
        ];
    }
}

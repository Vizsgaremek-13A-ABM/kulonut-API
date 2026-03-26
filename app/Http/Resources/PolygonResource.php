<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolygonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'polygon_id' => $this->id,
            'name'       => $this->name,
            'projects' => $this->whenLoaded('projects', function () use ($request) {
                return $this->projects->map(function ($project) {
                    return [
                        'project_id'      => $project->id,
                        'name'            => $project->project_name,
                        'plan_issue_date' => $project->plan_issue_date?->format('Y-m-d'),
                    ];
                });
            }),
            'coordinates' => CoordResource::collection($this->whenLoaded('coords')),
        ];
    }
}

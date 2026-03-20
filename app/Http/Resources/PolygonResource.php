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
            'name' => $this->name,
            'coordinates' => CoordResource::collection($this->whenLoaded('coords')),
        ];
    }
}

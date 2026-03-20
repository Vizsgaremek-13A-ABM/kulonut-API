<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'avatar' => $this->profile_icon ?? 'default-avatar.png',
            'role' => new RoleResource($this->whenLoaded('role')),
            'joined_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}

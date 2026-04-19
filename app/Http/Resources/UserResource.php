<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatar = $this->profile_icon ? (Str::startsWith($this->profile_icon, ['http://', 'https://']) ? $this->profile_icon : Storage::disk('public')->url($this->profile_icon)) : 'default-avatar.png';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'avatar' => $avatar,
            'role' => new RoleResource($this->whenLoaded('role')),
            'email_verified_at' => $this->email_verified_at,
            'joined_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}

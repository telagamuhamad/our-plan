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
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'timezone' => $this->timezone,
            'has_couple' => $this->couple_id !== null,
            'has_active_couple' => $this->hasActiveCouple(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

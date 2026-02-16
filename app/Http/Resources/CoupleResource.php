<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoupleResource extends JsonResource
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
            'invite_code' => $this->when($this->status === 'pending', $this->invite_code),
            'status' => $this->status,
            'user_one' => UserResource::make($this->whenLoaded('userOne')),
            'user_two' => UserResource::make($this->whenLoaded('userTwo')),
            'user_one_confirmed' => $this->when($this->user_one_confirmed_at !== null, true),
            'user_two_confirmed' => $this->when($this->user_two_confirmed_at !== null, true),
            'both_confirmed' => $this->canBeActivated(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

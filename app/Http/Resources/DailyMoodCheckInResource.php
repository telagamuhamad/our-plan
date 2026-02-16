<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DailyMoodCheckInResource extends JsonResource
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
            'mood' => $this->mood,
            'mood_emoji' => $this->mood_emoji,
            'mood_label' => $this->mood_label,
            'note' => $this->note,
            'check_in_date' => $this->check_in_date?->format('Y-m-d'),
            'formatted_date' => $this->formatted_date,
            'check_in_time' => $this->check_in_time?->format('H:i'),
            'is_updated' => $this->is_updated,
            'is_today' => $this->isToday(),
            'can_edit' => Auth::check() && $this->user_id === Auth::id() && $this->canUpdate(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'time_ago' => $this->time_ago,
            'commented_at' => $this->commented_at?->toIso8601String(),
            'author' => UserResource::make($this->whenLoaded('author')),
            'can_delete' => $this->when(
                Auth::check(),
                fn() => $this->user_id === Auth::id()
            ),
        ];
    }
}

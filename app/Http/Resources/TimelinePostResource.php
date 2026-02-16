<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TimelinePostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_type' => $this->post_type,
            'content' => $this->content,
            'attachment_url' => $this->attachment_url,
            'attachment_mime_type' => $this->attachment_mime_type,
            'time_ago' => $this->time_ago,
            'formatted_posted_at' => $this->formatted_posted_at,
            'posted_at' => $this->posted_at?->toIso8601String(),

            'author' => UserResource::make($this->whenLoaded('author')),

            'reactions' => PostReactionResource::collection($this->whenLoaded('reactions')),
            'reaction_summary' => $this->when(
                $this->relationLoaded('reactions'),
                fn() => $this->reaction_summary
            ),

            'comments_count' => $this->whenCounted('comments'),
            'recent_comments' => PostCommentResource::collection(
                $this->whenLoaded('comments')
            ),

            'can_edit' => $this->when(
                Auth::check(),
                fn() => $this->user_id === Auth::id()
            ),
            'can_delete' => $this->when(
                Auth::check(),
                fn() => $this->user_id === Auth::id()
            ),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => \App\Models\CoupleGoal::getCategories()[$this->category] ?? $this->category,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => \App\Models\CoupleGoal::getStatuses()[$this->status] ?? $this->status,
            'created_by' => $this->created_by,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'total_tasks' => $this->total_tasks,
            'completed_tasks' => $this->completed_tasks,
            'progress_percentage' => (float) $this->progress_percentage,
            'is_completed' => $this->isCompleted(),
            'is_in_progress' => $this->isInProgress(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'tasks' => \App\Http\Resources\TaskResource::collection($this->whenLoaded('tasks')),
            'creator' => \App\Http\Resources\UserResource::make($this->whenLoaded('creator')),
        ];
    }
}

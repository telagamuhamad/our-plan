<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'priority_label' => \App\Models\CoupleTask::getPriorities()[$this->priority] ?? $this->priority,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'assigned_to' => $this->assigned_to,
            'assigned_to_label' => \App\Models\CoupleTask::getAssignedToOptions()[$this->assigned_to] ?? $this->assigned_to,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'reminder_enabled' => $this->reminder_enabled,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'creator' => \App\Http\Resources\UserResource::make($this->whenLoaded('creator')),
            'completer' => \App\Http\Resources\UserResource::make($this->whenLoaded('completer')),
            'goal' => \App\Http\Resources\GoalResource::make($this->whenLoaded('goal')),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoupleTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'couple_id',
        'goal_id',
        'title',
        'description',
        'priority',
        'due_date',
        'assigned_to',
        'is_completed',
        'completed_by',
        'completed_at',
        'created_by',
        'reminder_enabled',
        'reminder_sent_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'is_completed' => 'boolean',
        'reminder_enabled' => 'boolean',
    ];

    /**
     * The couple that owns this task.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * The goal this task belongs to (optional).
     */
    public function goal()
    {
        return $this->belongsTo(CoupleGoal::class, 'goal_id');
    }

    /**
     * User who created this task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who completed this task.
     */
    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->is_completed;
    }

    /**
     * Check if task is due soon (within 3 days).
     */
    public function isDueSoon(): bool
    {
        return $this->due_date &&
               $this->due_date->diffInDays(now()) <= 3 &&
               $this->due_date->isFuture() &&
               !$this->is_completed;
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorities()[$this->priority] ?? $this->priority;
    }

    /**
     * Get assigned to label.
     */
    public function getAssignedToLabelAttribute(): string
    {
        return self::getAssignedToOptions()[$this->assigned_to] ?? $this->assigned_to;
    }

    /**
     * Get available priorities.
     */
    public static function getPriorities(): array
    {
        return [
            'low' => '🟢 Rendah',
            'medium' => '🟡 Sedang',
            'high' => '🔴 Tinggi',
        ];
    }

    /**
     * Get available assignment options.
     */
    public static function getAssignedToOptions(): array
    {
        return [
            'user_one' => 'Saya Sendiri',
            'user_two' => 'Pasangan',
            'both' => 'Berdua',
        ];
    }

    /**
     * Mark task as completed.
     */
    public function markAsCompleted(User $user): void
    {
        $this->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);

        // Update goal progress if task belongs to a goal
        if ($this->goal) {
            $this->goal->updateProgress();
        }
    }

    /**
     * Mark task as incomplete.
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        // Update goal progress if task belongs to a goal
        if ($this->goal) {
            $this->goal->updateProgress();
        }
    }

    /**
     * Scope for pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->where('is_completed', false);
    }

    /**
     * Scope for high priority tasks.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope for tasks due today.
     */
    public function scopeDueToday($query)
    {
        return $query->where('due_date', now()->toDateString())
            ->where('is_completed', false);
    }

    /**
     * Scope for standalone tasks (no goal).
     */
    public function scopeStandalone($query)
    {
        return $query->whereNull('goal_id');
    }

    /**
     * Scope for tasks assigned to specific user.
     */
    public function scopeAssignedTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $couple = $user->couple;
            if (!$couple) {
                return;
            }

            $q->where(function ($subQ) use ($couple) {
                $subQ->where('assigned_to', 'both');

                if ($couple->isUserOne($user)) {
                    $subQ->orWhere('assigned_to', 'user_one');
                }

                if ($couple->isUserTwo($user)) {
                    $subQ->orWhere('assigned_to', 'user_two');
                }
            });
        });
    }
}

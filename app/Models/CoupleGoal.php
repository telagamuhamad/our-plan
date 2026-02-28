<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoupleGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'couple_id',
        'title',
        'description',
        'category',
        'target_date',
        'status',
        'created_by',
        'completed_at',
        'total_tasks',
        'completed_tasks',
        'progress_percentage',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
    ];

    /**
     * The couple that owns this goal.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * User who created this goal.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tasks for this goal.
     */
    public function tasks()
    {
        return $this->hasMany(CoupleTask::class, 'goal_id');
    }

    /**
     * Pending tasks.
     */
    public function pendingTasks()
    {
        return $this->tasks()->where('is_completed', false);
    }

    /**
     * Completed tasks.
     */
    public function completedTasks()
    {
        return $this->tasks()->where('is_completed', true);
    }

    /**
     * Check if goal is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if goal is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            'travel' => '✈️ Travel',
            'financial' => '💰 Keuangan',
            'relationship' => '💕 Relationship',
            'personal' => '👤 Personal',
            'health' => '💪 Kesehatan',
            'career' => '💼 Karir',
            'other' => '📌 Lainnya',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Belum Mulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * Update progress.
     */
    public function updateProgress(): void
    {
        $this->refresh();
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('is_completed', true)->count();

        $this->update([
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'progress_percentage' => $total > 0 ? ($completed / $total) * 100 : 0,
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as in progress.
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Scope for active goals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope for completed goals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for upcoming goals (target date in future).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('target_date', '>=', now()->toDateString())
            ->orderBy('target_date');
    }

    /**
     * Scope for overdue goals.
     */
    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now()->toDateString())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled');
    }
}

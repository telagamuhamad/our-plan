<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saving extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'savings';

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'target_amount',
        'current_amount',
        'is_shared',
        'target_date',
        'completed_at',
        'last_notified_milestone',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'is_shared' => 'boolean',
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'last_notified_milestone' => 'integer',
    ];

    protected $appends = [
        'progress',
        'formatted_target_date',
        'days_remaining',
        'is_overdue',
        'is_completed',
        'daily_saving_needed',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(SavingCategory::class);
    }

    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute()
    {
        if ($this->target_amount == 0 || $this->is_shared) {
            return 0;
        }
        return min(100, round($this->current_amount / $this->target_amount * 100, 2));
    }

    /**
     * Get formatted target date
     */
    public function getFormattedTargetDateAttribute()
    {
        if (!$this->target_date) {
            return null;
        }
        return Carbon::parse($this->target_date)->format('j F Y');
    }

    /**
     * Get days remaining until target date
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->target_date) {
            return null;
        }

        $targetDate = Carbon::parse($this->target_date)->endOfDay();
        $now = Carbon::now();

        if ($this->completed_at) {
            return 0;
        }

        return $now->diffInDays($targetDate, false);
    }

    /**
     * Check if saving is overdue (target date passed but not completed)
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->target_date || $this->completed_at || $this->is_shared) {
            return false;
        }

        return Carbon::parse($this->target_date)->isPast();
    }

    /**
     * Check if saving is completed
     */
    public function getIsCompletedAttribute()
    {
        return $this->completed_at !== null;
    }

    /**
     * Calculate monthly saving needed to reach target
     */
    public function getDailySavingNeededAttribute()
    {
        if (!$this->target_date || $this->is_shared || $this->completed_at) {
            return null;
        }

        $targetDate = Carbon::parse($this->target_date)->endOfDay();
        $now = Carbon::now();

        if ($targetDate->isPast()) {
            return null;
        }

        $monthsRemaining = $now->diffInMonths($targetDate);
        $daysRemaining = $now->diffInDays($targetDate);

        // Use months if >= 1 month, otherwise use days as fraction of month
        if ($monthsRemaining >= 1) {
            $remainingAmount = $this->target_amount - $this->current_amount;
            if ($remainingAmount <= 0) {
                return 0;
            }
            return $remainingAmount / max(1, $monthsRemaining);
        }

        // Less than a month - calculate as if it's 1 month
        $remainingAmount = $this->target_amount - $this->current_amount;
        if ($remainingAmount <= 0) {
            return 0;
        }

        if ($daysRemaining <= 0) {
            return $remainingAmount;
        }

        // Treat remaining days as fraction of a month (30 days)
        return $remainingAmount * 30 / max(1, $daysRemaining);
    }

    /**
     * Alias for monthly saving needed (for clarity in views)
     */
    public function getMonthlySavingNeededAttribute()
    {
        return $this->daily_saving_needed;
    }

    /**
     * Get status label for display
     */
    public function getStatusAttribute()
    {
        if ($this->completed_at) {
            return 'completed';
        }

        if ($this->is_shared) {
            return 'general';
        }

        if ($this->is_overdue) {
            return 'overdue';
        }

        if ($this->progress >= 100) {
            return 'ready_to_complete';
        }

        if ($this->days_remaining !== null && $this->days_remaining <= 7) {
            return 'urgent';
        }

        return 'on_track';
    }

    /**
     * Get countdown data like Meeting model
     */
    public function getCountdownAttribute()
    {
        if (!$this->target_date || $this->completed_at) {
            return null;
        }

        $now = Carbon::now();
        $target = Carbon::parse($this->target_date)->endOfDay();

        if ($now->gte($target)) {
            return [
                'is_overdue' => true,
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'message' => 'Target date telah terlewati',
            ];
        }

        $diff = $now->diff($target);
        $totalSeconds = $target->diffInSeconds($now);

        return [
            'is_overdue' => false,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'message' => sprintf('%d hari lagi menuju target!', $diff->d),
            'total_seconds' => $totalSeconds,
        ];
    }

    /**
     * Scope for active goals (has target, not completed, not shared)
     */
    public function scopeActiveGoals($query)
    {
        return $query->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at');
    }

    /**
     * Scope for overdue savings
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_shared', false)
            ->whereNotNull('target_date')
            ->whereDate('target_date', '<', Carbon::now()->toDateString())
            ->whereNull('completed_at');
    }

    /**
     * Scope for upcoming deadlines (within X days)
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('is_shared', false)
            ->whereNotNull('target_date')
            ->whereDate('target_date', '>', Carbon::now()->toDateString())
            ->whereDate('target_date', '<=', Carbon::now()->addDays($days)->toDateString())
            ->whereNull('completed_at')
            ->orderBy('target_date', 'asc');
    }

    /**
     * Scope for completed savings
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Check and mark as completed if progress >= 100%
     */
    public function checkAndMarkCompleted(): bool
    {
        if ($this->is_shared || !$this->target_amount) {
            return false;
        }

        if ($this->current_amount >= $this->target_amount && !$this->completed_at) {
            $this->update(['completed_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Get the next milestone to notify (25, 50, 75, 100)
     */
    public function getNextMilestone(): ?int
    {
        if ($this->is_shared || !$this->target_amount) {
            return null;
        }

        $currentProgress = $this->progress;
        $lastNotified = $this->last_notified_milestone;

        $milestones = [25, 50, 75, 100];

        foreach ($milestones as $milestone) {
            if ($milestone > $lastNotified && $currentProgress >= $milestone) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * Check if should notify milestone
     */
    public function shouldNotifyMilestone(): bool
    {
        return $this->getNextMilestone() !== null;
    }

    /**
     * Update last notified milestone
     */
    public function updateLastNotifiedMilestone(int $milestone): void
    {
        $this->update(['last_notified_milestone' => $milestone]);
    }
}

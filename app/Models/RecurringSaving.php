<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringSaving extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'saving_id',
        'user_id',
        'frequency',
        'amount',
        'name',
        'start_date',
        'next_run_date',
        'last_run_date',
        'end_date',
        'is_active',
        'paused_at',
        'skip_count',
        'total_deposits',
        'total_deposited_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_deposited_amount' => 'decimal:2',
        'start_date' => 'date',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'end_date' => 'date',
        'paused_at' => 'datetime',
        'is_active' => 'boolean',
        'skip_count' => 'integer',
        'total_deposits' => 'integer',
    ];

    protected $appends = [
        'formatted_amount',
        'formatted_frequency',
        'is_paused',
        'is_due',
        'progress_percentage',
    ];

    public function savingModel()
    {
        return $this->belongsTo(Saving::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedFrequencyAttribute()
    {
        return match($this->frequency) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'biweekly' => 'Dua Mingguan',
            'monthly' => 'Bulanan',
            default => $this->frequency,
        };
    }

    public function getIsPausedAttribute()
    {
        return $this->paused_at !== null;
    }

    public function getIsDueAttribute()
    {
        if (!$this->is_active || $this->is_paused) {
            return false;
        }

        if ($this->end_date && Carbon::parse($this->end_date)->isPast()) {
            return false;
        }

        return Carbon::parse($this->next_run_date)->isToday() || Carbon::parse($this->next_run_date)->isPast();
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->end_date && $this->start_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            $now = Carbon::now();

            if ($now->gte($end)) return 100;
            if ($now->lte($start)) return 0;

            $total = $end->diffInDays($start);
            $elapsed = $now->diffInDays($start);

            return min(100, round(($elapsed / $total) * 100));
        }

        if ($this->savingModel && $this->savingModel->target_amount > 0) {
            return round(($this->total_deposited_amount / $this->savingModel->target_amount) * 100);
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('paused_at');
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->whereNull('paused_at')
            ->whereDate('next_run_date', '<=', Carbon::today()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', Carbon::today()->toDateString());
            });
    }

    public function calculateNextRunDate(): Carbon
    {
        $currentDate = $this->last_run_date ? Carbon::parse($this->last_run_date) : Carbon::parse($this->start_date);

        return match($this->frequency) {
            'daily' => $currentDate->addDay(),
            'weekly' => $currentDate->addWeek(),
            'biweekly' => $currentDate->addWeeks(2),
            'monthly' => $currentDate->addMonth(),
            default => $currentDate->addMonth(),
        };
    }

    public function pause(): bool
    {
        return $this->update(['paused_at' => now()]);
    }

    public function resume(): bool
    {
        $this->update(['paused_at' => null]);

        if (Carbon::parse($this->next_run_date)->isPast()) {
            $this->update(['next_run_date' => $this->calculateNextRunDate()]);
        }

        return true;
    }

    public function skip(): bool
    {
        $nextRun = $this->calculateNextRunDate();
        return $this->update([
            'next_run_date' => $nextRun,
            'skip_count' => $this->skip_count + 1,
        ]);
    }

    public function markAsProcessed(): bool
    {
        $nextRunDate = $this->calculateNextRunDate();

        if ($this->end_date && $nextRunDate->gt(Carbon::parse($this->end_date))) {
            return $this->update([
                'last_run_date' => $this->next_run_date,
                'next_run_date' => $nextRunDate,
                'is_active' => false,
            ]);
        }

        return $this->update([
            'last_run_date' => $this->next_run_date,
            'next_run_date' => $nextRunDate,
            'total_deposits' => $this->total_deposits + 1,
            'total_deposited_amount' => $this->total_deposited_amount + $this->amount,
        ]);
    }
}

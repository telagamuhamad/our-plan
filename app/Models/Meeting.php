<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'travelling_user_id',
        'meeting_date',
        'location',
        'is_departure_transport_ready',
        'is_return_transport_ready',
        'is_rest_place_ready',
        'note',
        'start_date',
        'end_date'
    ];

    protected $appends = [
        'formatted_start_date',
        'formatted_end_date',
        'average_rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'travelling_user_id');
    }

    public function travels()
    {
        return $this->hasMany(Travel::class, 'meeting_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(MeetingFeedback::class);
    }

    public function getFormattedStartDateAttribute()
    {
        return Carbon::parse($this->start_date)->format('j F Y');
    }

    public function getFormattedEndDateAttribute()
    {
        return Carbon::parse($this->end_date)->format('j F Y');
    }

    public function getAverageRatingAttribute()
    {
        if ($this->relationLoaded('feedbacks') && $this->feedbacks->isNotEmpty()) {
            return round($this->feedbacks->avg('rating'), 1);
        }

        return $this->feedbacks()->avg('rating') ?? 0;
    }

    /**
     * Get countdown data until meeting starts
     */
    public function getCountdownAttribute()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start_date)->startOfDay();

        if ($now->gte($start)) {
            // Meeting has already started or ended
            $end = Carbon::parse($this->end_date)->endOfDay();

            if ($now->lte($end)) {
                // Meeting is in progress
                return [
                    'is_in_progress' => true,
                    'is_passed' => false,
                    'days' => 0,
                    'hours' => 0,
                    'minutes' => 0,
                    'seconds' => 0,
                    'message' => 'Meeting sedang berlangsung! 🎉',
                    'total_seconds' => 0,
                ];
            }

            // Meeting has ended
            return [
                'is_in_progress' => false,
                'is_passed' => true,
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'message' => 'Meeting telah selesai',
                'total_seconds' => 0,
            ];
        }

        // Calculate countdown
        $diff = $now->diff($start);
        $totalSeconds = $start->diffInSeconds($now);

        return [
            'is_in_progress' => false,
            'is_passed' => false,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'message' => 'Menuju ketemu lagi! ❤️',
            'total_seconds' => $totalSeconds,
        ];
    }

    /**
     * Scope to get upcoming meetings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', Carbon::now()->toDateString())
            ->orderBy('start_date', 'asc');
    }

    /**
     * Scope to get the next meeting
     */
    public function scopeNext($query)
    {
        return $query->where('start_date', '>=', Carbon::now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->limit(1);
    }

    /**
     * Get formatted countdown for display
     */
    public function getFormattedCountdownAttribute()
    {
        $countdown = $this->countdown;

        if ($countdown['is_in_progress']) {
            return '🎉 Sedang berlangsung!';
        }

        if ($countdown['is_passed']) {
            return '✅ Selesai';
        }

        $parts = [];
        if ($countdown['days'] > 0) {
            $parts[] = "{$countdown['days']} hari";
        }
        if ($countdown['hours'] > 0) {
            $parts[] = "{$countdown['hours']} jam";
        }
        if ($countdown['minutes'] > 0 || empty($parts)) {
            $parts[] = "{$countdown['minutes']} menit";
        }

        return implode(', ', $parts);
    }
}

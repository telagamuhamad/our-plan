<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMoodCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'couple_id',
        'user_id',
        'mood',
        'note',
        'check_in_date',
        'check_in_time',
        'is_updated',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_in_time' => 'datetime',
        'is_updated' => 'boolean',
    ];

    protected $appends = [
        'mood_emoji',
        'formatted_date',
    ];

    /**
     * Mood emoji mapping
     */
    private const MOOD_EMOJIS = [
        'happy' => '😊',
        'sad' => '😢',
        'angry' => '😡',
        'loved' => '❤️',
        'tired' => '😴',
        'anxious' => '😰',
        'excited' => '🤩',
    ];

    /**
     * Get the couple that owns the mood check-in.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the user that owns the mood check-in.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter moods by couple.
     */
    public function scopeForCouple($query, $coupleId)
    {
        return $query->where('couple_id', $coupleId);
    }

    /**
     * Scope to filter moods by date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('check_in_date', $date);
    }

    /**
     * Scope to get today's moods.
     */
    public function scopeToday($query)
    {
        return $query->where('check_in_date', today()->toDateString());
    }

    /**
     * Get mood emoji attribute.
     */
    public function getMoodEmojiAttribute(): string
    {
        return self::MOOD_EMOJIS[$this->mood] ?? '😐';
    }

    /**
     * Get formatted date attribute.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->check_in_date->translatedFormat('l, d F Y');
    }

    /**
     * Check if this mood check-in is from today.
     */
    public function isToday(): bool
    {
        return $this->check_in_date->isToday();
    }

    /**
     * Check if this mood can be updated (only if it's today).
     */
    public function canUpdate(): bool
    {
        return $this->isToday();
    }

    /**
     * Get all available moods with emojis.
     */
    public static function getAvailableMoods(): array
    {
        return self::MOOD_EMOJIS;
    }

    /**
     * Get mood label in Indonesian.
     */
    public function getMoodLabelAttribute(): string
    {
        return match($this->mood) {
            'happy' => 'Senang',
            'sad' => 'Sedih',
            'angry' => 'Marah',
            'loved' => 'Kasih Sayang',
            'tired' => 'Lelah',
            'anxious' => 'Cemas',
            'excited' => 'Semangat',
            default => 'Biasa',
        };
    }
}

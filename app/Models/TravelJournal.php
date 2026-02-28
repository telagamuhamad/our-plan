<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelJournal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'travel_journals';

    protected $fillable = [
        'travel_id',
        'user_id',
        'title',
        'content',
        'journal_date',
        'mood',
        'weather',
        'location',
        'is_favorite',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'is_favorite' => 'boolean',
    ];

    protected $appends = [
        'formatted_journal_date',
        'mood_emoji',
    ];

    /**
     * Get the travel that owns the journal.
     */
    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }

    /**
     * Get the user who wrote the journal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted journal date
     */
    public function getFormattedJournalDateAttribute()
    {
        return $this->journal_date
            ? Carbon::parse($this->journal_date)->format('j F Y')
            : '-';
    }

    /**
     * Get mood emoji
     */
    public function getMoodEmojiAttribute()
    {
        $moods = [
            'happy' => '😊',
            'excited' => '🤩',
            'love' => '🥰',
            'sad' => '😢',
            'tired' => '😫',
            'adventurous' => '🤠',
            'relaxed' => '😌',
            'surprised' => '😲',
        ];

        return $moods[$this->mood] ?? '😊';
    }

    /**
     * Scope to get journals by travel
     */
    public function scopeByTravel($query, $travelId)
    {
        return $query->where('travel_id', $travelId);
    }

    /**
     * Scope to get favorite journals
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to order by journal date
     */
    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('journal_date', $direction)->orderBy('created_at', $direction);
    }

    /**
     * Scope to search by title or content
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Get excerpt from content
     */
    public function getExcerptAttribute($length = 100)
    {
        return strlen($this->content) > $length
            ? substr($this->content, 0, $length) . '...'
            : $this->content;
    }
}

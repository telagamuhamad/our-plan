<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meeting_feedbacks';

    protected $fillable = [
        'meeting_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the meeting that owns the feedback.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Get the user that owns the feedback.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted rating as stars
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Scope to get feedback by meeting
     */
    public function scopeByMeeting($query, $meetingId)
    {
        return $query->where('meeting_id', $meetingId);
    }

    /**
     * Scope to get feedback by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

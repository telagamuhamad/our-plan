<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimelinePost extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'couple_id',
        'user_id',
        'post_type',
        'content',
        'attachment_path',
        'attachment_mime_type',
        'attachment_size_bytes',
        'posted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'posted_at' => 'datetime',
        'attachment_size_bytes' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'time_ago',
        'formatted_posted_at',
    ];

    /**
     * Get the couple that owns the post.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the author of the post.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all reactions for the post.
     */
    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id');
    }

    /**
     * Get all comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')
            ->orderBy('commented_at', 'desc');
    }

    /**
     * Scope to posts for a specific couple.
     */
    public function scopeForCouple($query, $coupleId)
    {
        return $query->where('couple_id', $coupleId);
    }

    /**
     * Scope to posts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('post_type', $type);
    }

    /**
     * Scope to posts with attachments.
     */
    public function scopeWithAttachment($query)
    {
        return $query->whereNotNull('attachment_path');
    }

    /**
     * Get time ago attribute (e.g., "2 hours ago").
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->posted_at->diffForHumans();
    }

    /**
     * Get formatted posted date attribute.
     */
    public function getFormattedPostedAtAttribute(): string
    {
        return $this->posted_at->format('j F Y, H:i');
    }

    /**
     * Check if post has attachment.
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    /**
     * Get attachment URL.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }
        return asset('storage/' . $this->attachment_path);
    }

    /**
     * Get reaction summary grouped by emoji.
     */
    public function getReactionSummaryAttribute(): array
    {
        return $this->reactions()
            ->selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->pluck('count', 'emoji')
            ->toArray();
    }

    /**
     * Check if user has reacted to this post.
     */
    public function hasUserReacted($userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) {
            return false;
        }
        return $this->reactions()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's reaction to this post.
     */
    public function getUserReaction($userId = null): ?PostReaction
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) {
            return null;
        }
        return $this->reactions()->where('user_id', $userId)->first();
    }

    /**
     * Get recent comments (limit 3 for preview).
     */
    public function getRecentCommentsAttribute()
    {
        return $this->comments()->take(3)->get();
    }

    /**
     * Get total comments count.
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Check if this post belongs to the given couple.
     */
    public function belongsToCouple($coupleId): bool
    {
        return $this->couple_id === $coupleId;
    }

    /**
     * Check if the post is owned by the given user.
     */
    public function isOwnedBy($userId): bool
    {
        return $this->user_id === $userId;
    }
}

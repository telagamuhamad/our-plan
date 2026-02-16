<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'emoji',
    ];

    /**
     * Emoji mapping for display.
     *
     * @var array<string, string>
     */
    public const EMOJI_MAP = [
        'heart' => '❤️',
        'laugh' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    /**
     * Get the post that was reacted to.
     */
    public function post()
    {
        return $this->belongsTo(TimelinePost::class, 'post_id');
    }

    /**
     * Get the user who reacted.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the emoji character.
     */
    public function getEmojiCharAttribute(): string
    {
        return self::EMOJI_MAP[$this->emoji] ?? '?';
    }

    /**
     * Scope to reactions for a specific post.
     */
    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Scope to reactions by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to reactions of a specific emoji type.
     */
    public function scopeOfType($query, $emoji)
    {
        return $query->where('emoji', $emoji);
    }

    /**
     * Check if this reaction belongs to the given user.
     */
    public function isByUser($userId): bool
    {
        return $this->user_id === $userId;
    }
}

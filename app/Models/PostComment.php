<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'commented_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commented_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'time_ago',
    ];

    /**
     * Get the post being commented on.
     */
    public function post()
    {
        return $this->belongsTo(TimelinePost::class, 'post_id');
    }

    /**
     * Get the comment author.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to comments for a specific post.
     */
    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Scope to comments by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get time ago attribute.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->commented_at->diffForHumans();
    }

    /**
     * Check if comment belongs to couple through post.
     */
    public function belongsToCouple($coupleId): bool
    {
        return $this->post && $this->post->couple_id === $coupleId;
    }

    /**
     * Check if this comment is owned by the given user.
     */
    public function isOwnedBy($userId): bool
    {
        return $this->user_id === $userId;
    }
}

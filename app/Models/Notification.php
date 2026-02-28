<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'couple_id',
        'type',
        'post_id',
        'actor_id',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * The user who receives the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The couple associated with the notification.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * The post associated with the notification.
     */
    public function post()
    {
        return $this->belongsTo(TimelinePost::class, 'post_id');
    }

    /**
     * The user who triggered the notification.
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Mark as read.
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Mark as unread.
     */
    public function markAsUnread(): bool
    {
        $this->is_read = false;
        $this->read_at = null;
        return $this->save();
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'couple_id',
        'avatar_url',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the couple that the user belongs to.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the partner of this user.
     */
    public function partner()
    {
        if (!$this->couple) {
            return null;
        }
        return $this->couple->getPartner($this);
    }

    /**
     * Check if the user has an active couple.
     */
    public function hasActiveCouple(): bool
    {
        return $this->couple && $this->couple->isActive();
    }

    /**
     * Check if the user is user one in the couple.
     */
    public function isUserOne(): bool
    {
        return $this->couple && $this->couple->user_one_id === $this->id;
    }

    /**
     * Check if the user is user two in the couple.
     */
    public function isUserTwo(): bool
    {
        return $this->couple && $this->couple->user_two_id === $this->id;
    }

    /**
     * Check if the user belongs to any couple (pending or active).
     */
    public function hasCouple(): bool
    {
        return $this->couple !== null;
    }

    /**
     * Scope to get users without a couple.
     */
    public function scopeWithoutCouple($query)
    {
        return $query->whereNull('couple_id');
    }

    /**
     * Scope to get users with a couple.
     */
    public function scopeWithCouple($query)
    {
        return $query->whereNotNull('couple_id');
    }

    /**
     * Scope to get users with an active couple.
     */
    public function scopeWithActiveCouple($query)
    {
        return $query->whereHas('couple', function ($query) {
            $query->where('status', 'active');
        });
    }

    /**
     * Get timeline posts created by the user.
     */
    public function timelinePosts()
    {
        return $this->hasMany(TimelinePost::class, 'user_id');
    }

    /**
     * Get reactions made by the user.
     */
    public function postReactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * Get comments made by the user.
     */
    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Get notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->unread()->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Get mood check-ins for the user.
     */
    public function moodCheckIns()
    {
        return $this->hasMany(DailyMoodCheckIn::class)->orderBy('check_in_date', 'desc');
    }

    /**
     * Get today's mood check-in for the user.
     */
    public function todayMoodCheckIn()
    {
        return $this->hasOne(DailyMoodCheckIn::class)->where('check_in_date', today()->toDateString());
    }
}

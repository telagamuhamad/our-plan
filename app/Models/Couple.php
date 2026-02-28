<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Couple extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invite_code',
        'user_one_id',
        'user_two_id',
        'status',
        'user_one_confirmed_at',
        'user_two_confirmed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_one_confirmed_at' => 'datetime',
        'user_two_confirmed_at' => 'datetime',
    ];

    /**
     * Get the user one of the couple.
     */
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Get the user two of the couple.
     */
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * Get all users belonging to this couple.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all timeline posts for this couple.
     */
    public function timelinePosts()
    {
        return $this->hasMany(TimelinePost::class)->orderBy('posted_at', 'desc');
    }

    /**
     * Get all mood check-ins for this couple.
     */
    public function moodCheckIns()
    {
        return $this->hasMany(DailyMoodCheckIn::class)->orderBy('check_in_date', 'desc');
    }

    /**
     * Get today's mood check-ins for this couple.
     */
    public function todayMoodCheckIns()
    {
        return $this->hasMany(DailyMoodCheckIn::class)->where('check_in_date', today()->toDateString());
    }

    /**
     * Scope to filter pending couples.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter active couples.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to find by invite code.
     */
    public function scopeWithInviteCode($query, string $code)
    {
        return $query->where('invite_code', $code);
    }

    /**
     * Scope to find couple by user ID.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        });
    }

    /**
     * Check if the couple is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the couple is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the partner of the given user.
     */
    public function getPartner(User $user): ?User
    {
        if ($this->user_one_id === $user->id) {
            return $this->userTwo;
        }
        if ($this->user_two_id === $user->id) {
            return $this->userOne;
        }
        return null;
    }

    /**
     * Check if both users have confirmed and the couple can be activated.
     */
    public function canBeActivated(): bool
    {
        return $this->user_one_id !== null
            && $this->user_two_id !== null
            && $this->user_one_confirmed_at !== null
            && $this->user_two_confirmed_at !== null;
    }

    /**
     * Check if the given user is user one in the couple.
     */
    public function isUserOne(User $user): bool
    {
        return $this->user_one_id === $user->id;
    }

    /**
     * Check if the given user is user two in the couple.
     */
    public function isUserTwo(User $user): bool
    {
        return $this->user_two_id === $user->id;
    }

    /**
     * Check if a user belongs to this couple.
     */
    public function hasUser(User $user): bool
    {
        return $this->user_one_id === $user->id || $this->user_two_id === $user->id;
    }

    /**
     * Activate the couple.
     */
    public function activate(): bool
    {
        if (!$this->canBeActivated()) {
            return false;
        }

        return $this->update(['status' => 'active']);
    }

    /**
     * Check if user one has confirmed.
     */
    public function hasUserOneConfirmed(): bool
    {
        return $this->user_one_confirmed_at !== null;
    }

    /**
     * Check if user two has confirmed.
     */
    public function hasUserTwoConfirmed(): bool
    {
        return $this->user_two_id !== null && $this->user_two_confirmed_at !== null;
    }
}

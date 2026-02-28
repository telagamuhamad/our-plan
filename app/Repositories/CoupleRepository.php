<?php

namespace App\Repositories;

use App\Models\Couple;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CoupleRepository
{
    protected Couple $model;

    public function __construct(Couple $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new couple with invite code for the user.
     */
    public function createInviteCode(User $user): Couple
    {
        do {
            $inviteCode = $this->generateUniqueCode();
        } while ($this->findByInviteCode($inviteCode) !== null);

        $couple = $this->model->create([
            'invite_code' => $inviteCode,
            'user_one_id' => $user->id,
            'status' => 'pending',
        ]);

        // Update user's couple_id
        $user->update(['couple_id' => $couple->id]);

        return $couple;
    }

    /**
     * Find a couple by invite code.
     */
    public function findByInviteCode(string $code): ?Couple
    {
        return $this->model->where('invite_code', $code)->first();
    }

    /**
     * Find a couple by user ID.
     */
    public function findByUserId(int $userId): ?Couple
    {
        return $this->model->where(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })->first();
    }

    /**
     * Find a couple by user.
     */
    public function findByUser(User $user): ?Couple
    {
        return $this->findByUserId($user->id);
    }

    /**
     * Join a couple using invite code.
     */
    public function joinCouple(string $inviteCode, int $userId): ?Couple
    {
        $couple = $this->findByInviteCode($inviteCode);

        if (!$couple || $couple->user_two_id !== null) {
            return null;
        }

        // Prevent user from joining their own invite
        if ($couple->user_one_id === $userId) {
            return null;
        }

        $couple->update(['user_two_id' => $userId]);

        return $couple->fresh();
    }

    /**
     * Confirm pairing for a user.
     */
    public function confirmPairing(int $coupleId, int $userId): bool
    {
        $couple = $this->model->find($coupleId);

        if (!$couple) {
            return false;
        }

        $updateData = [];
        if ($couple->user_one_id === $userId && !$couple->user_one_confirmed_at) {
            $updateData['user_one_confirmed_at'] = now();
        } elseif ($couple->user_two_id === $userId && !$couple->user_two_confirmed_at) {
            $updateData['user_two_confirmed_at'] = now();
        }

        if (empty($updateData)) {
            return false;
        }

        $couple->update($updateData);

        // Auto-activate if both confirmed
        if ($couple->canBeActivated()) {
            $couple->update(['status' => 'active']);
        }

        return true;
    }

    /**
     * Activate a couple.
     */
    public function activateCouple(int $coupleId): bool
    {
        $couple = $this->model->find($coupleId);
        if (!$couple || !$couple->canBeActivated()) {
            return false;
        }

        return $couple->update(['status' => 'active']);
    }

    /**
     * Get couple information with users loaded.
     */
    public function getCoupleWithUsers(int $coupleId): ?Couple
    {
        return $this->model->with(['userOne', 'userTwo'])->find($coupleId);
    }

    /**
     * Get couple for a user with users loaded.
     */
    public function findByUserWithUsers(User $user): ?Couple
    {
        return $this->model->where(function ($query) use ($user) {
            $query->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        })
            ->with(['userOne', 'userTwo'])
            ->first();
    }

    /**
     * Remove user from couple (leave couple).
     * When any user leaves, both users are unpaired and the couple is deleted.
     */
    public function leaveCouple(User $user): bool
    {
        if (!$user->couple_id) {
            return false;
        }

        $couple = $this->model->find($user->couple_id);
        if (!$couple) {
            return false;
        }

        // Nullify couple_id for both users
        if ($couple->userOne) {
            $couple->userOne->update(['couple_id' => null]);
        }
        if ($couple->userTwo) {
            $couple->userTwo->update(['couple_id' => null]);
        }

        // Delete the couple entirely
        return $couple->delete();
    }

    /**
     * Delete a couple entirely.
     */
    public function deleteCouple(int $coupleId): bool
    {
        $couple = $this->model->find($coupleId);
        if (!$couple) {
            return false;
        }

        // Nullify couple_id for both users
        if ($couple->userOne) {
            $couple->userOne->update(['couple_id' => null]);
        }
        if ($couple->userTwo) {
            $couple->userTwo->update(['couple_id' => null]);
        }

        return $couple->delete();
    }

    /**
     * Generate a unique 6-character invite code.
     * Excludes confusing characters: 0, O, I, 1.
     */
    private function generateUniqueCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle(str_repeat($characters, 2)), 0, 6);
    }

    /**
     * Get the model instance.
     */
    public function getModel(): Couple
    {
        return $this->model;
    }

    /**
     * Get query builder for couples.
     */
    public function query(): Builder
    {
        return $this->model->query();
    }
}

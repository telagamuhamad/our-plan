<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\User;
use App\Repositories\CoupleRepository;
use Exception;

class PairingService
{
    protected CoupleRepository $repository;

    public function __construct(CoupleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new invite code for the user.
     *
     * @throws Exception If user already has a couple.
     */
    public function createInviteCode(User $user): Couple
    {
        if ($user->couple_id) {
            throw new Exception('Anda sudah terhubung dengan pasangan.');
        }

        return $this->repository->createInviteCode($user);
    }

    /**
     * Join a couple using invite code.
     *
     * @throws Exception If validation fails.
     */
    public function joinCouple(string $inviteCode, User $user): Couple
    {
        if ($user->couple_id) {
            throw new Exception('Anda sudah terhubung dengan pasangan.');
        }

        $couple = $this->repository->findByInviteCode($inviteCode);

        if (!$couple) {
            throw new Exception('Kode undangan tidak valid.');
        }

        if ($couple->user_two_id !== null) {
            throw new Exception('Kode undangan ini sudah digunakan.');
        }

        if ((int)$couple->user_one_id === (int)$user->id) {
            throw new Exception('Anda tidak bisa bergabung dengan undangan sendiri.');
        }

        $updatedCouple = $this->repository->joinCouple($inviteCode, $user->id);

        if (!$updatedCouple) {
            throw new Exception('Gagal bergabung dengan pasangan.');
        }

        // Update user's couple_id
        $user->update(['couple_id' => $updatedCouple->id]);

        return $updatedCouple;
    }

    /**
     * Confirm pairing from either user.
     *
     * @throws Exception If validation fails.
     */
    public function confirmPairing(int $coupleId, User $user): Couple
    {
        $couple = $this->repository->getCoupleWithUsers($coupleId);

        if (!$couple) {
            throw new Exception('Pasangan tidak ditemukan.');
        }

        if (!$couple->hasUser($user)) {
            throw new Exception('Anda tidak memiliki akses ke pasangan ini.');
        }

        if ($couple->isActive()) {
            throw new Exception('Pasangan sudah aktif.');
        }

        $result = $this->repository->confirmPairing($coupleId, $user->id);

        if (!$result) {
            throw new Exception('Gagal mengkonfirmasi pasangan.');
        }

        return $couple->fresh();
    }

    /**
     * Get couple information for user.
     */
    public function getCoupleInfo(User $user): ?array
    {
        $couple = $this->repository->findByUserWithUsers($user);

        if (!$couple) {
            return null;
        }

        return [
            'id' => $couple->id,
            'invite_code' => $couple->invite_code,
            'status' => $couple->status,
            'user_one_confirmed' => $couple->hasUserOneConfirmed(),
            'user_two_confirmed' => $couple->hasUserTwoConfirmed(),
            'user_one_id' => $couple->user_one_id,
            'user_two_id' => $couple->user_two_id,
            'partner' => $couple->getPartner($user),
            'is_user_one' => $couple->isUserOne($user),
            'can_confirm' => !$this->hasUserConfirmed($couple, $user),
            'both_confirmed' => $couple->canBeActivated(),
        ];
    }

    /**
     * Leave a couple.
     *
     * @throws Exception If validation fails.
     */
    public function leaveCouple(User $user): bool
    {
        if (!$user->couple_id) {
            throw new Exception('Anda tidak terhubung dengan pasangan manapun.');
        }

        return $this->repository->leaveCouple($user);
    }

    /**
     * Check if user can access couple data.
     */
    public function userBelongsToCouple(User $user, int $coupleId): bool
    {
        $couple = $this->repository->findByUser($user);
        return $couple && $couple->id === $coupleId;
    }

    /**
     * Check if the given user has confirmed the pairing.
     */
    protected function hasUserConfirmed(Couple $couple, User $user): bool
    {
        if ((int)$couple->user_one_id === (int)$user->id) {
            return $couple->hasUserOneConfirmed();
        }
        if ((int)$couple->user_two_id === (int)$user->id) {
            return $couple->hasUserTwoConfirmed();
        }
        return false;
    }

    /**
     * Get couple by ID with users loaded.
     */
    public function getCoupleById(int $coupleId): ?Couple
    {
        return $this->repository->getCoupleWithUsers($coupleId);
    }

    /**
     * Get couple for user.
     */
    public function getCoupleForUser(User $user): ?Couple
    {
        return $this->repository->findByUserWithUsers($user);
    }

    /**
     * Check if user can create a new invite code.
     */
    public function canCreateInvite(User $user): bool
    {
        return $user->couple_id === null;
    }

    /**
     * Check if user can join a couple.
     */
    public function canJoinCouple(User $user): bool
    {
        return $user->couple_id === null;
    }

    /**
     * Validate invite code format.
     */
    public function validateInviteCode(string $code): bool
    {
        return preg_match('/^[A-Z0-9]{6}$/', $code) === 1;
    }

    /**
     * Normalize invite code to uppercase.
     */
    public function normalizeInviteCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}

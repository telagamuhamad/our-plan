<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\DailyMoodCheckIn;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\DailyMoodRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyMoodService
{
    protected DailyMoodRepository $repository;

    public function __construct(DailyMoodRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Check in or update today's mood for a user.
     */
    public function checkIn(User $user, string $mood, ?string $note = null): DailyMoodCheckIn
    {
        $couple = $user->couple;
        if (!$couple || !$couple->isActive()) {
            throw new Exception('Anda belum memiliki pasangan aktif');
        }

        $existing = $this->repository->findTodayByUser($user->id);
        $isUpdate = $existing !== null;

        DB::beginTransaction();
        try {
            $moodCheckIn = $this->repository->createOrUpdate($couple, $user, $mood, $note, $existing);

            // Create notification for partner only on new check-in (not update)
            if (!$isUpdate) {
                $this->notifyPartner($couple, $user, $moodCheckIn);
            }

            DB::commit();
            return $moodCheckIn;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get today's moods for a couple.
     */
    public function getTodayMoods(Couple $couple): Collection
    {
        return $this->repository->findTodayByCouple($couple->id);
    }

    /**
     * Get mood history for a couple.
     */
    public function getMoodHistory(Couple $couple, int $days = 30): Collection
    {
        return $this->repository->getCoupleMoods($couple->id, $days);
    }

    /**
     * Get mood statistics for a couple.
     */
    public function getMoodStats(Couple $couple, int $days = 30): array
    {
        return $this->repository->getMoodStats($couple->id, $days);
    }

    /**
     * Update mood check-in.
     */
    public function updateMood(DailyMoodCheckIn $mood, string $newMood, ?string $note = null): DailyMoodCheckIn
    {
        if (!$mood->canUpdate()) {
            throw new Exception('Hanya bisa mengupdate mood hari ini');
        }

        if ($mood->user_id !== Auth::id()) {
            throw new Exception('Anda tidak memiliki akses untuk mengupdate mood ini');
        }

        $mood->update([
            'mood' => $newMood,
            'note' => $note,
            'is_updated' => true,
        ]);

        // Notify partner about mood update
        $user = $mood->user;
        $couple = $user->couple;
        if ($couple && $couple->isActive()) {
            $this->notifyPartnerUpdate($couple, $user, $mood);
        }

        return $mood->fresh();
    }

    /**
     * Delete mood check-in.
     */
    public function deleteMood(DailyMoodCheckIn $mood): bool
    {
        if ($mood->user_id !== Auth::id()) {
            throw new Exception('Anda tidak memiliki akses untuk menghapus mood ini');
        }

        return $this->repository->delete($mood);
    }

    /**
     * Find mood check-in by ID.
     */
    public function findMood(int $id): ?DailyMoodCheckIn
    {
        return $this->repository->findWithRelations($id);
    }

    /**
     * Find mood check-in by ID for current user.
     */
    public function findMoodForUser(int $id, int $userId): ?DailyMoodCheckIn
    {
        return $this->repository->findForUser($id, $userId);
    }

    /**
     * Check if user has checked in today.
     */
    public function hasCheckedInToday(int $userId): bool
    {
        return $this->repository->hasCheckedInToday($userId);
    }

    /**
     * Get mood history by date range.
     */
    public function getByDateRange(Couple $couple, string $startDate, string $endDate): Collection
    {
        return $this->repository->getByDateRange($couple->id, $startDate, $endDate);
    }

    /**
     * Notify partner about mood check-in.
     */
    protected function notifyPartner(Couple $couple, User $user, DailyMoodCheckIn $mood): void
    {
        $partner = $couple->getPartner($user);
        if (!$partner) {
            return;
        }

        Notification::create([
            'user_id' => $partner->id,
            'couple_id' => $couple->id,
            'type' => 'mood_check_in',
            'title' => 'Mood Check-in Baru',
            'message' => "{$user->name} baru saja check-in mood: {$mood->mood_emoji}",
            'data' => json_encode([
                'mood_id' => $mood->id,
                'mood' => $mood->mood,
                'mood_emoji' => $mood->mood_emoji,
            ]),
            'is_read' => false,
        ]);
    }

    /**
     * Notify partner about mood update.
     */
    protected function notifyPartnerUpdate(Couple $couple, User $user, DailyMoodCheckIn $mood): void
    {
        $partner = $couple->getPartner($user);
        if (!$partner) {
            return;
        }

        Notification::create([
            'user_id' => $partner->id,
            'couple_id' => $couple->id,
            'type' => 'mood_update',
            'title' => 'Mood Diupdate',
            'message' => "{$user->name} mengupdate mood menjadi: {$mood->mood_emoji}",
            'data' => json_encode([
                'mood_id' => $mood->id,
                'mood' => $mood->mood,
                'mood_emoji' => $mood->mood_emoji,
            ]),
            'is_read' => false,
        ]);
    }
}

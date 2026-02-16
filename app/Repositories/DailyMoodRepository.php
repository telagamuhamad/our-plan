<?php

namespace App\Repositories;

use App\Models\Couple;
use App\Models\DailyMoodCheckIn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DailyMoodRepository
{
    /**
     * Find today's mood check-in for a user.
     */
    public function findTodayByUser(int $userId): ?DailyMoodCheckIn
    {
        return DailyMoodCheckIn::where('user_id', $userId)
            ->where('check_in_date', today()->toDateString())
            ->first();
    }

    /**
     * Find today's mood check-ins for a couple.
     */
    public function findTodayByCouple(int $coupleId): Collection
    {
        return DailyMoodCheckIn::where('couple_id', $coupleId)
            ->where('check_in_date', today()->toDateString())
            ->get();
    }

    /**
     * Create or update a mood check-in.
     */
    public function createOrUpdate(
        Couple $couple,
        User $user,
        string $mood,
        ?string $note = null,
        ?DailyMoodCheckIn $existing = null
    ): DailyMoodCheckIn {
        $data = [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'mood' => $mood,
            'note' => $note,
            'check_in_date' => today()->toDateString(),
            'check_in_time' => now()->format('H:i:s'),
        ];

        if ($existing) {
            $existing->update(array_merge($data, ['is_updated' => true]));
            return $existing->fresh();
        }

        return DailyMoodCheckIn::create($data);
    }

    /**
     * Get mood history for a couple.
     */
    public function getCoupleMoods(int $coupleId, int $days = 30): Collection
    {
        $startDate = today()->subDays($days - 1);

        return DailyMoodCheckIn::where('couple_id', $coupleId)
            ->where('check_in_date', '>=', $startDate)
            ->orderBy('check_in_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->with('user')
            ->get();
    }

    /**
     * Get mood statistics for a couple.
     */
    public function getMoodStats(int $coupleId, int $days = 30): array
    {
        $startDate = today()->subDays($days - 1);

        $moods = DailyMoodCheckIn::where('couple_id', $coupleId)
            ->where('check_in_date', '>=', $startDate)
            ->get();

        $stats = [
            'happy' => 0,
            'sad' => 0,
            'angry' => 0,
            'loved' => 0,
            'tired' => 0,
            'anxious' => 0,
            'excited' => 0,
            'total' => $moods->count(),
        ];

        foreach ($moods as $mood) {
            if (isset($stats[$mood->mood])) {
                $stats[$mood->mood]++;
            }
        }

        return $stats;
    }

    /**
     * Get mood check-in by ID with relationships.
     */
    public function findWithRelations(int $id): ?DailyMoodCheckIn
    {
        return DailyMoodCheckIn::with(['user', 'couple'])->find($id);
    }

    /**
     * Get mood check-in by ID for a specific user.
     */
    public function findForUser(int $id, int $userId): ?DailyMoodCheckIn
    {
        return DailyMoodCheckIn::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Delete a mood check-in.
     */
    public function delete(DailyMoodCheckIn $mood): bool
    {
        return $mood->delete();
    }

    /**
     * Get mood history by date range.
     */
    public function getByDateRange(int $coupleId, string $startDate, string $endDate): Collection
    {
        return DailyMoodCheckIn::where('couple_id', $coupleId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->orderBy('check_in_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->with('user')
            ->get();
    }

    /**
     * Check if user has checked in today.
     */
    public function hasCheckedInToday(int $userId): bool
    {
        return DailyMoodCheckIn::where('user_id', $userId)
            ->where('check_in_date', today()->toDateString())
            ->exists();
    }
}

<?php

namespace App\Services;

use App\Models\CoupleGoal;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GoalService
{
    /**
     * Get all goals for a couple.
     */
    public function getGoals(int $coupleId, ?string $status = null, ?string $category = null, bool $withTasks = false)
    {
        $query = CoupleGoal::where('couple_id', $coupleId)
            ->with('creator');

        if ($status) {
            if ($status === 'active') {
                $query->whereIn('status', ['pending', 'in_progress']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($withTasks) {
            $query->with('tasks');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get goal by ID with relationships.
     */
    public function getGoal(int $goalId, int $coupleId): ?CoupleGoal
    {
        return CoupleGoal::where('couple_id', $coupleId)
            ->where('id', $goalId)
            ->with('tasks.creator', 'tasks.completer', 'creator')
            ->first();
    }

    /**
     * Create a new goal.
     */
    public function createGoal(int $coupleId, int $userId, array $data): CoupleGoal
    {
        DB::beginTransaction();
        try {
            $goal = CoupleGoal::create([
                'couple_id' => $coupleId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'target_date' => $data['target_date'] ?? null,
                'status' => 'pending',
                'created_by' => $userId,
            ]);

            // Add tasks if provided
            if (!empty($data['tasks'])) {
                foreach ($data['tasks'] as $taskData) {
                    $goal->tasks()->create([
                        'couple_id' => $coupleId,
                        'title' => $taskData['title'],
                        'description' => $taskData['description'] ?? null,
                        'priority' => $taskData['priority'] ?? 'medium',
                        'due_date' => $taskData['due_date'] ?? null,
                        'assigned_to' => $taskData['assigned_to'] ?? 'both',
                        'created_by' => $userId,
                    ]);
                }
                $goal->updateProgress();
            }

            DB::commit();
            return $goal->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a goal.
     */
    public function updateGoal(int $goalId, array $data): ?CoupleGoal
    {
        DB::beginTransaction();
        try {
            $goal = CoupleGoal::find($goalId);

            if (!$goal) {
                return null;
            }

            $goal->update([
                'title' => $data['title'] ?? $goal->title,
                'description' => $data['description'] ?? $goal->description,
                'category' => $data['category'] ?? $goal->category,
                'target_date' => $data['target_date'] ?? $goal->target_date,
                'status' => $data['status'] ?? $goal->status,
            ]);

            // Update completion timestamp
            if (isset($data['status']) && $data['status'] === 'completed' && !$goal->completed_at) {
                $goal->update(['completed_at' => now()]);
            }

            DB::commit();
            return $goal->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a goal.
     */
    public function deleteGoal(int $goalId): bool
    {
        $goal = CoupleGoal::find($goalId);

        if (!$goal) {
            return false;
        }

        return $goal->delete();
    }

    /**
     * Get goal statistics.
     */
    public function getStats(int $coupleId): array
    {
        $total = CoupleGoal::where('couple_id', $coupleId)->count();
        $completed = CoupleGoal::where('couple_id', $coupleId)
            ->where('status', 'completed')
            ->count();
        $active = CoupleGoal::where('couple_id', $coupleId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // Calculate overall progress
        $goals = CoupleGoal::where('couple_id', $coupleId)->get();
        $totalProgress = $goals->sum('progress_percentage');
        $overallProgress = $total > 0 ? round($totalProgress / $total, 1) : 0;

        return [
            'total_goals' => $total,
            'active_goals' => $active,
            'completed_goals' => $completed,
            'overall_progress' => $overallProgress,
        ];
    }

    /**
     * Get upcoming goals with target dates.
     */
    public function getUpcomingGoals(int $coupleId, int $limit = 5): Collection
    {
        return CoupleGoal::where('couple_id', $coupleId)
            ->whereNotNull('target_date')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->orderBy('target_date')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Services;

use App\Models\CoupleTask;
use App\Models\CoupleGoal;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Get all tasks for a couple.
     */
    public function getTasks(int $coupleId, ?string $status = null, ?string $priority = null, ?int $goalId = null)
    {
        $query = CoupleTask::where('couple_id', $coupleId)
            ->with('goal', 'creator');

        if ($status) {
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'pending') {
                $query->where('is_completed', false);
            }
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($goalId) {
            $query->where('goal_id', $goalId);
        }

        return $query->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending tasks for a couple.
     */
    public function getPendingTasks(int $coupleId, int $limit = 10): Collection
    {
        return CoupleTask::where('couple_id', $coupleId)
            ->where('is_completed', false)
            ->with('goal', 'creator')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get tasks assigned to specific user.
     */
    public function getTasksForUser(User $user): Collection
    {
        if (!$user->couple) {
            return new Collection();
        }

        $couple = $user->couple;

        return CoupleTask::where('couple_id', $couple->id)
            ->where(function ($q) use ($couple, $user) {
                $q->where(function ($subQ) use ($couple, $user) {
                    $subQ->where('assigned_to', 'both');

                    if ($couple->isUserOne($user)) {
                        $subQ->orWhere('assigned_to', 'user_one');
                    }

                    if ($couple->isUserTwo($user)) {
                        $subQ->orWhere('assigned_to', 'user_two');
                    }
                });
            })
            ->with('goal', 'creator')
            ->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get task by ID.
     */
    public function getTask(int $taskId, int $coupleId): ?CoupleTask
    {
        return CoupleTask::where('couple_id', $coupleId)
            ->where('id', $taskId)
            ->with('goal', 'creator', 'completer')
            ->first();
    }

    /**
     * Create a new task.
     */
    public function createTask(array $data): CoupleTask
    {
        DB::beginTransaction();
        try {
            $task = CoupleTask::create([
                'couple_id' => $data['couple_id'],
                'goal_id' => $data['goal_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => $data['due_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? 'both',
                'reminder_enabled' => $data['reminder_enabled'] ?? false,
                'created_by' => $data['created_by'],
            ]);

            // Update goal progress if task belongs to a goal
            if ($task->goal) {
                $task->goal->updateProgress();
            }

            DB::commit();
            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a task.
     */
    public function updateTask(int $taskId, array $data): ?CoupleTask
    {
        $task = CoupleTask::find($taskId);

        if (!$task) {
            return null;
        }

        $task->update([
            'title' => $data['title'] ?? $task->title,
            'description' => $data['description'] ?? $task->description,
            'priority' => $data['priority'] ?? $task->priority,
            'due_date' => $data['due_date'] ?? $task->due_date,
            'assigned_to' => $data['assigned_to'] ?? $task->assigned_to,
            'reminder_enabled' => $data['reminder_enabled'] ?? $task->reminder_enabled,
        ]);

        return $task->fresh();
    }

    /**
     * Toggle task completion.
     */
    public function toggleComplete(int $taskId, User $user): CoupleTask
    {
        $task = CoupleTask::find($taskId);

        if (!$task) {
            throw new Exception('Task not found');
        }

        if ($task->is_completed) {
            $task->markAsIncomplete();
        } else {
            $task->markAsCompleted($user);
        }

        return $task->fresh();
    }

    /**
     * Delete a task.
     */
    public function deleteTask(int $taskId): bool
    {
        $task = CoupleTask::find($taskId);

        if (!$task) {
            return false;
        }

        $goalId = $task->goal_id;
        $result = $task->delete();

        // Update goal progress if task belonged to a goal
        if ($goalId) {
            $goal = CoupleGoal::find($goalId);
            if ($goal) {
                $goal->updateProgress();
            }
        }

        return $result;
    }

    /**
     * Get task statistics.
     */
    public function getStats(int $coupleId): array
    {
        $total = CoupleTask::where('couple_id', $coupleId)->count();
        $completed = CoupleTask::where('couple_id', $coupleId)
            ->where('is_completed', true)
            ->count();
        $pending = $total - $completed;

        $overdue = CoupleTask::where('couple_id', $coupleId)
            ->overdue()
            ->count();

        // Calculate user's pending tasks (assigned to current user or both)
        $myPending = CoupleTask::where('couple_id', $coupleId)
            ->where('is_completed', false)
            ->whereIn('assigned_to', ['both', 'user_one']) // Simplified
            ->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'pending_tasks' => $pending,
            'my_pending' => $myPending,
            'overdue_count' => $overdue,
            'completion_rate' => $completionRate,
        ];
    }
}

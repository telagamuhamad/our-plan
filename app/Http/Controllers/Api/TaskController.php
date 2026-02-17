<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $status = $request->query('status');
        $priority = $request->query('priority');
        $goalId = $request->query('goal_id');
        $assignedTo = $request->query('assigned_to');

        $tasks = $this->taskService->getTasks(
            coupleId: $user->couple->id,
            status: $status,
            priority: $priority,
            goalId: $goalId,
            assignedTo: $assignedTo
        );

        return response()->json([
            'data' => TaskResource::collection($tasks->load('goal', 'creator')),
        ]);
    }

    /**
     * Get pending tasks for the user.
     */
    public function pending(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $tasks = $this->taskService->getPendingTasks($user->couple->id);

        return response()->json([
            'data' => TaskResource::collection($tasks->load('goal', 'creator')),
        ]);
    }

    /**
     * Get tasks for the current user.
     */
    public function myTasks(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $tasks = $this->taskService->getTasksForUser($user);

        return response()->json([
            'data' => TaskResource::collection($tasks->load('goal', 'creator')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $validated = $request->validate([
            'goal_id' => 'nullable|exists:couple_goals,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date|after:today',
            'assigned_to' => 'nullable|in:user_one,user_two,both',
            'reminder_enabled' => 'nullable|boolean',
        ]);

        try {
            $validated['couple_id'] = $user->couple->id;
            $validated['created_by'] = $user->id;

            $task = $this->taskService->createTask($validated);

            return response()->json([
                'message' => 'Task berhasil dibuat.',
                'data' => new TaskResource($task->load('goal', 'creator')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'data' => new TaskResource($task->load('goal', 'creator', 'completer')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|in:user_one,user_two,both',
            'reminder_enabled' => 'nullable|boolean',
        ]);

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task tidak ditemukan.',
                ], 404);
            }

            $task = $this->taskService->updateTask($id, $validated);

            return response()->json([
                'message' => 'Task berhasil diupdate.',
                'data' => new TaskResource($task->load('goal', 'creator')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle task completion status.
     */
    public function toggle(string $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task tidak ditemukan.',
                ], 404);
            }

            $task = $this->taskService->toggleComplete($id, $user);

            return response()->json([
                'message' => $task->is_completed ? 'Task berhasil diselesaikan.' : 'Task dibatalkan.',
                'data' => new TaskResource($task->load('goal', 'creator', 'completer')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah status task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task tidak ditemukan.',
                ], 404);
            }

            $this->taskService->deleteTask($id);

            return response()->json([
                'message' => 'Task berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get task statistics.
     */
    public function stats(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        try {
            $stats = $this->taskService->getStats($user->couple->id);

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

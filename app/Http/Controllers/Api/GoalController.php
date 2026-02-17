<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GoalResource;
use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoalController extends Controller
{
    protected GoalService $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
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
        $category = $request->query('category');
        $withTasks = $request->query('with_tasks', 'false') === 'true';

        $goals = $this->goalService->getGoals(
            coupleId: $user->couple->id,
            status: $status,
            category: $category,
            withTasks: $withTasks
        );

        return response()->json([
            'data' => GoalResource::collection($goals),
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|in:travel,financial,relationship,health,education,career,home,other',
            'target_date' => 'nullable|date|after:today',
            'tasks' => 'nullable|array',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'nullable|string|max:1000',
            'tasks.*.priority' => 'nullable|in:low,medium,high',
            'tasks.*.due_date' => 'nullable|date|after:today',
            'tasks.*.assigned_to' => 'nullable|in:user_one,user_two,both',
        ]);

        try {
            $goal = $this->goalService->createGoal($user->couple->id, $user->id, $validated);

            return response()->json([
                'message' => 'Goal berhasil dibuat.',
                'data' => new GoalResource($goal->load('tasks')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat goal.',
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
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return response()->json([
                    'message' => 'Goal tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'data' => new GoalResource($goal->load('tasks', 'creator')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data goal.',
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
            'category' => 'nullable|in:travel,financial,relationship,health,education,career,home,other',
            'target_date' => 'nullable|date|after:today',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        try {
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return response()->json([
                    'message' => 'Goal tidak ditemukan.',
                ], 404);
            }

            $goal = $this->goalService->updateGoal($id, $validated);

            return response()->json([
                'message' => 'Goal berhasil diupdate.',
                'data' => new GoalResource($goal->load('tasks')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate goal.',
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
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return response()->json([
                    'message' => 'Goal tidak ditemukan.',
                ], 404);
            }

            $this->goalService->deleteGoal($id);

            return response()->json([
                'message' => 'Goal berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus goal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get goal statistics.
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
            $stats = $this->goalService->getStats($user->couple->id);

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

    /**
     * Get upcoming goals.
     */
    public function upcoming(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        try {
            $goals = $this->goalService->getUpcomingGoals($user->couple->id);

            return response()->json([
                'data' => GoalResource::collection($goals),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data upcoming goals.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

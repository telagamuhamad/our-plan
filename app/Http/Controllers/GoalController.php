<?php

namespace App\Http\Controllers;

use App\Services\GoalService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    protected GoalService $goalService;
    protected TaskService $taskService;

    public function __construct(GoalService $goalService, TaskService $taskService)
    {
        $this->goalService = $goalService;
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of goals.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('goals.no-couple');
        }

        $status = $request->query('status', 'active');
        $category = $request->query('category');

        $goals = $this->goalService->getGoals(
            coupleId: $user->couple->id,
            status: $status === 'all' ? null : $status,
            category: $category,
            withTasks: true
        );

        $stats = $this->goalService->getStats($user->couple->id);
        $categories = \App\Models\CoupleGoal::getCategories();
        $statuses = \App\Models\CoupleGoal::getStatuses();

        return view('goals.index', compact('goals', 'stats', 'categories', 'statuses'));
    }

    /**
     * Show the form for creating a new goal.
     */
    public function create(): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('goals.no-couple');
        }

        $categories = \App\Models\CoupleGoal::getCategories();

        return view('goals.create', compact('categories'));
    }

    /**
     * Store a newly created goal.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
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
            $this->goalService->createGoal($user->couple->id, $user->id, $validated);

            return redirect()->route('goals.index')->with('success', 'Goal berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat goal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified goal.
     */
    public function show(string $id): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('goals.no-couple');
        }

        $goal = $this->goalService->getGoal($id, $user->couple->id);

        if (!$goal) {
            abort(404, 'Goal tidak ditemukan.');
        }

        $goal->load('tasks', 'creator');

        return view('goals.show', compact('goal'));
    }

    /**
     * Show the form for editing the specified goal.
     */
    public function edit(string $id): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('goals.no-couple');
        }

        $goal = $this->goalService->getGoal($id, $user->couple->id);

        if (!$goal) {
            abort(404, 'Goal tidak ditemukan.');
        }

        $categories = \App\Models\CoupleGoal::getCategories();
        $statuses = \App\Models\CoupleGoal::getStatuses();

        return view('goals.edit', compact('goal', 'categories', 'statuses'));
    }

    /**
     * Update the specified goal.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
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
                return redirect()->back()->with('error', 'Goal tidak ditemukan.');
            }

            $this->goalService->updateGoal($id, $validated);

            return redirect()->route('goals.show', $id)->with('success', 'Goal berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate goal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified goal.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
        }

        try {
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return redirect()->back()->with('error', 'Goal tidak ditemukan.');
            }

            $this->goalService->deleteGoal($id);

            return redirect()->route('goals.index')->with('success', 'Goal berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus goal: ' . $e->getMessage());
        }
    }

    /**
     * Get goal statistics (AJAX).
     */
    public function stats()
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $stats = $this->goalService->getStats($user->couple->id);

        return response()->json(['data' => $stats]);
    }

    /**
     * Mark goal as completed.
     */
    public function markCompleted(string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
        }

        try {
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return redirect()->back()->with('error', 'Goal tidak ditemukan.');
            }

            $goal->markAsCompleted();

            return redirect()->route('goals.show', $id)->with('success', 'Goal berhasil diselesaikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyelesaikan goal: ' . $e->getMessage());
        }
    }

    /**
     * Mark goal as in progress.
     */
    public function markInProgress(string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
        }

        try {
            $goal = $this->goalService->getGoal($id, $user->couple->id);

            if (!$goal) {
                return redirect()->back()->with('error', 'Goal tidak ditemukan.');
            }

            $goal->markAsInProgress();

            return redirect()->route('goals.show', $id)->with('success', 'Goal status diubah menjadi in progress.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate status goal: ' . $e->getMessage());
        }
    }
}

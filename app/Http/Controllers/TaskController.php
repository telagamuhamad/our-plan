<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected GoalService $goalService;

    public function __construct(TaskService $taskService, GoalService $goalService)
    {
        $this->taskService = $taskService;
        $this->goalService = $goalService;
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('tasks.no-couple');
        }

        $status = $request->query('status', 'pending');
        $priority = $request->query('priority');
        $goalId = $request->query('goal_id');

        $tasks = $this->taskService->getTasks(
            coupleId: $user->couple->id,
            status: $status === 'all' ? null : $status,
            priority: $priority,
            goalId: $goalId
        )->load('goal', 'creator');

        $myTasks = $this->taskService->getTasksForUser($user)->load('goal', 'creator');
        $stats = $this->taskService->getStats($user->couple->id);

        $goals = $this->goalService->getGoals($user->couple->id, withTasks: false);
        $priorities = \App\Models\CoupleTask::getPriorities();
        $assignedOptions = \App\Models\CoupleTask::getAssignedToOptions();

        return view('tasks.index', compact('tasks', 'myTasks', 'stats', 'goals', 'priorities', 'assignedOptions'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('tasks.no-couple');
        }

        $goals = $this->goalService->getGoals($user->couple->id, status: 'active', withTasks: false);
        $priorities = \App\Models\CoupleTask::getPriorities();
        $assignedOptions = \App\Models\CoupleTask::getAssignedToOptions();

        return view('tasks.create', compact('goals', 'priorities', 'assignedOptions'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
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

            $this->taskService->createTask($validated);

            return redirect()->route('tasks.index')->with('success', 'Task berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat task: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('tasks.no-couple');
        }

        $task = $this->taskService->getTask($id, $user->couple->id);

        if (!$task) {
            abort(404, 'Task tidak ditemukan.');
        }

        $task->load('goal', 'creator', 'completer');

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(string $id): View
    {
        $user = auth()->user();

        if (!$user->couple) {
            return view('tasks.no-couple');
        }

        $task = $this->taskService->getTask($id, $user->couple->id);

        if (!$task) {
            abort(404, 'Task tidak ditemukan.');
        }

        $goals = $this->goalService->getGoals($user->couple->id, status: 'active', withTasks: false);
        $priorities = \App\Models\CoupleTask::getPriorities();
        $assignedOptions = \App\Models\CoupleTask::getAssignedToOptions();

        return view('tasks.edit', compact('task', 'goals', 'priorities', 'assignedOptions'));
    }

    /**
     * Update the specified task.
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
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|in:user_one,user_two,both',
            'reminder_enabled' => 'nullable|boolean',
        ]);

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return redirect()->back()->with('error', 'Task tidak ditemukan.');
            }

            $this->taskService->updateTask($id, $validated);

            return redirect()->route('tasks.show', $id)->with('success', 'Task berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate task: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
        }

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return redirect()->back()->with('error', 'Task tidak ditemukan.');
            }

            $this->taskService->deleteTask($id);

            return redirect()->route('tasks.index')->with('success', 'Task berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus task: ' . $e->getMessage());
        }
    }

    /**
     * Toggle task completion status.
     */
    public function toggle(string $id)
    {
        $user = auth()->user();

        if (!$user->couple) {
            return redirect()->back()->with('error', 'Anda belum terhubung dengan pasangan.');
        }

        try {
            $task = $this->taskService->getTask($id, $user->couple->id);

            if (!$task) {
                return redirect()->back()->with('error', 'Task tidak ditemukan.');
            }

            $task = $this->taskService->toggleComplete($id, $user);

            $message = $task->is_completed ? 'Task berhasil diselesaikan.' : 'Task dibatalkan.';

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status task: ' . $e->getMessage());
        }
    }

    /**
     * Get task statistics (AJAX).
     */
    public function stats()
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $stats = $this->taskService->getStats($user->couple->id);

        return response()->json(['data' => $stats]);
    }

    /**
     * Get pending tasks for dashboard (AJAX).
     */
    public function pending()
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $tasks = $this->taskService->getPendingTasks($user->couple->id)->load('goal', 'creator');

        return response()->json([
            'data' => \App\Http\Resources\TaskResource::collection($tasks),
        ]);
    }

    /**
     * Get tasks for current user (AJAX).
     */
    public function myTasks()
    {
        $user = auth()->user();

        if (!$user->couple) {
            return response()->json([
                'message' => 'Anda belum terhubung dengan pasangan.',
            ], 403);
        }

        $tasks = $this->taskService->getTasksForUser($user)->load('goal', 'creator');

        return response()->json([
            'data' => \App\Http\Resources\TaskResource::collection($tasks),
        ]);
    }
}

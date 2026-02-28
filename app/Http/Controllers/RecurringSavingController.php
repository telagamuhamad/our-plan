<?php

namespace App\Http\Controllers;

use App\Services\RecurringSavingService;
use App\Services\SavingService;
use Illuminate\Http\Request;

class RecurringSavingController extends Controller
{
    protected $recurringSavingService;
    protected $savingService;

    public function __construct(
        RecurringSavingService $recurringSavingService,
        SavingService $savingService
    ) {
        $this->recurringSavingService = $recurringSavingService;
        $this->savingService = $savingService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $recurrings = $this->recurringSavingService->getAllForUser($request->user());
            return response()->json(['data' => $recurrings]);
        }

        $recurrings = $this->recurringSavingService->getAllForUser($request->user());
        $stats = $this->recurringSavingService->getStatsForUser($request->user());
        $savings = $this->savingService->getAllSavings();

        return view('recurring-savings.index', [
            'recurrings' => $recurrings,
            'stats' => $stats,
            'savings' => $savings,
        ]);
    }

    public function show(Request $request, $id)
    {
        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        return response()->json(['data' => $recurring]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'saving_id' => 'required|exists:savings,id',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly',
            'amount' => 'required|numeric|min:1000',
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            $recurring = $this->recurringSavingService->create($validated, $request->user());

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Recurring saving created successfully',
                    'data' => $recurring->load('saving'),
                ], 201);
            }

            return back()->with('success', 'Auto-save berhasil dibuat!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to create recurring saving',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal membuat auto-save: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'frequency' => 'sometimes|in:daily,weekly,biweekly,monthly',
            'amount' => 'sometimes|numeric|min:1000',
            'name' => 'nullable|string|max:255',
            'end_date' => 'nullable|date',
        ]);

        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        try {
            $this->recurringSavingService->update($id, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Recurring saving updated successfully',
                    'data' => $recurring->fresh()->load('saving'),
                ]);
            }

            return back()->with('success', 'Auto-save berhasil diperbarui!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to update recurring saving',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal memperbarui auto-save: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $this->recurringSavingService->delete($id);

        if ($request->ajax()) {
            return response()->json(['message' => 'Recurring saving deleted successfully']);
        }

        return back()->with('success', 'Auto-save berhasil dihapus!');
    }

    public function pause(Request $request, $id)
    {
        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        try {
            $this->recurringSavingService->pause($id);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Recurring saving paused successfully',
                    'data' => $recurring->fresh(),
                ]);
            }

            return back()->with('success', 'Auto-save berhasil dijeda!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to pause recurring saving',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal menjeda auto-save: ' . $e->getMessage());
        }
    }

    public function resume(Request $request, $id)
    {
        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        try {
            $this->recurringSavingService->resume($id);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Recurring saving resumed successfully',
                    'data' => $recurring->fresh(),
                ]);
            }

            return back()->with('success', 'Auto-save berhasil dilanjutkan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to resume recurring saving',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal melanjutkan auto-save: ' . $e->getMessage());
        }
    }

    public function skip(Request $request, $id)
    {
        $recurring = $this->recurringSavingService->find($id);

        if (!$recurring) {
            return back()->with('error', 'Recurring saving not found.');
        }

        if ($recurring->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        try {
            $this->recurringSavingService->skip($id);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Next occurrence skipped successfully',
                    'data' => $recurring->fresh(),
                ]);
            }

            return back()->with('success', 'Jadwal auto-save berhasil dilewati!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to skip occurrence',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal melewati jadwal: ' . $e->getMessage());
        }
    }

    public function stats(Request $request)
    {
        $stats = $this->recurringSavingService->getStatsForUser($request->user());

        return response()->json(['data' => $stats]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\SavingsComparisonService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SavingsComparisonController extends Controller
{
    public function __construct(
        private SavingsComparisonService $comparisonService
    ) {}

    /**
     * Display comparison dashboard
     */
    public function index(Request $request)
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return back()->with('error', 'Anda belum terhubung dengan partner. Hubungkan terlebih dahulu untuk melihat perbandingan.');
        }

        if ($request->ajax()) {
            return response()->json(['data' => $comparison]);
        }

        return view('savings.comparison', [
            'comparison' => $comparison,
        ]);
    }

    /**
     * Get overview comparison
     */
    public function overview(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['overview'],
        ]);
    }

    /**
     * Get savings list comparison
     */
    public function savingsList(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['savings_list'],
        ]);
    }

    /**
     * Get monthly contributions
     */
    public function monthlyContributions(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['monthly_contributions'],
        ]);
    }

    /**
     * Get category comparison
     */
    public function categories(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['by_category'],
        ]);
    }

    /**
     * Get goals progress comparison
     */
    public function goals(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['goals_progress'],
        ]);
    }

    /**
     * Get achievements comparison
     */
    public function achievements(Request $request): JsonResponse
    {
        $comparison = $this->comparisonService->getComparisonData($request->user());

        if (!$comparison) {
            return response()->json(['error' => 'No partner found'], 404);
        }

        return response()->json([
            'data' => $comparison['achievements'],
        ]);
    }
}

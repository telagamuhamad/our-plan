<?php

namespace App\Http\Controllers;

use App\Services\SavingsAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SavingsAnalyticsController extends Controller
{
    public function __construct(
        private SavingsAnalyticsService $analyticsService
    ) {}

    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all');

        // Map period to growth period
        $growthPeriod = match($period) {
            'all' => '1year',
            'year' => '1year',
            'quarter' => '3months',
            'month' => '1month',
            default => '6months',
        };

        if ($request->ajax()) {
            $analytics = $this->analyticsService->getUserAnalytics($request->user(), $period);
            return response()->json(['data' => $analytics]);
        }

        $analytics = $this->analyticsService->getUserAnalytics($request->user(), $period);
        $upcoming = $this->analyticsService->getUpcomingTargets($request->user());
        $growth = $this->analyticsService->getSavingsGrowth($request->user(), $growthPeriod);
        $categoryDistribution = $this->analyticsService->getCategoryDistribution($request->user());

        return view('savings.analytics', [
            'analytics' => $analytics,
            'upcoming' => $upcoming,
            'growth' => $growth,
            'categoryDistribution' => $categoryDistribution,
            'period' => $period,
        ]);
    }

    /**
     * Get overview statistics
     */
    public function overview(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getUserAnalytics(
            $request->user(),
            $request->get('period', 'all')
        );

        return response()->json([
            'data' => $analytics['overview'],
        ]);
    }

    /**
     * Get savings trends
     */
    public function trends(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getUserAnalytics(
            $request->user(),
            $request->get('period', 'all')
        );

        return response()->json([
            'data' => $analytics['trends'],
        ]);
    }

    /**
     * Get goals progress
     */
    public function goals(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getUserAnalytics(
            $request->user(),
            $request->get('period', 'all')
        );

        return response()->json([
            'data' => $analytics['goals_progress'],
        ]);
    }

    /**
     * Get savings growth data for chart
     */
    public function growth(Request $request): JsonResponse
    {
        $period = $request->get('period', '6months');
        $growth = $this->analyticsService->getSavingsGrowth($request->user(), $period);

        return response()->json([
            'data' => $growth,
        ]);
    }

    /**
     * Get category distribution
     */
    public function categories(Request $request): JsonResponse
    {
        $distribution = $this->analyticsService->getCategoryDistribution($request->user());

        return response()->json([
            'data' => $distribution,
        ]);
    }

    /**
     * Get upcoming targets
     */
    public function upcoming(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $upcoming = $this->analyticsService->getUpcomingTargets($request->user(), $limit);

        return response()->json([
            'data' => $upcoming,
        ]);
    }

    /**
     * Compare periods
     */
    public function compare(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $comparison = $this->analyticsService->comparePeriods($request->user(), $period);

        return response()->json([
            'data' => $comparison,
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        $analytics = $this->analyticsService->getUserAnalytics($request->user(), $period);

        return response()->json([
            'data' => $analytics,
            'exported_at' => now()->format('Y-m-d H:i:s'),
            'period' => $period,
        ]);
    }
}

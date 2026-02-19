<?php

namespace App\Http\Controllers;

use App\Services\TravelService;

class TravelAnalyticsController extends Controller
{
    protected $travelService;

    public function __construct(TravelService $travelService)
    {
        $this->travelService = $travelService;
    }

    /**
     * Display travel analytics page
     */
    public function index()
    {
        $analytics = $this->travelService->getAnalytics();

        return view('travels.analytics', [
            'analytics' => $analytics
        ]);
    }

    /**
     * Get analytics data (AJAX/JSON)
     */
    public function data()
    {
        $analytics = $this->travelService->getAnalytics();

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
}

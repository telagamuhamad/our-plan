<?php

namespace App\Http\Controllers;

use App\Services\MeetingService;

class MeetingAnalyticsController extends Controller
{
    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    /**
     * Display meeting analytics page
     */
    public function index()
    {
        $analytics = $this->meetingService->getAnalytics();

        return view('meetings.analytics', [
            'analytics' => $analytics
        ]);
    }

    /**
     * Get analytics data (AJAX/JSON)
     */
    public function data()
    {
        $analytics = $this->meetingService->getAnalytics();

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
}

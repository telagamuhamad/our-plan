<?php

namespace App\Http\Controllers;

use App\Services\MeetingService;

class DashboardController extends Controller
{
    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $countdown = null;

        // Only get countdown if user belongs to a couple
        if (auth()->check() && auth()->user()->couple_id) {
            $countdown = $this->meetingService->getCountdown();
        }

        return view('dashboard', [
            'countdown' => $countdown
        ]);
    }
}

<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Check if user has active couple
        if (!$user->couple) {
            return redirect()->route('pairing.status');
        }

        return view('dashboard');
    }
}

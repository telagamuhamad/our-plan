<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMoodCheckInRequest;
use App\Models\DailyMoodCheckIn;
use App\Services\DailyMoodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyMoodController extends Controller
{
    protected DailyMoodService $service;

    public function __construct(DailyMoodService $service)
    {
        $this->service = $service;
    }

    /**
     * Display mood check-in page with history and stats.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return redirect()->route('pairing.status')
                ->with('error', 'Anda belum memiliki pasangan aktif');
        }

        $days = (int) $request->get('days', 30);
        $todayMoods = $this->service->getTodayMoods($couple);
        $moodHistory = $this->service->getMoodHistory($couple, $days);
        $moodStats = $this->service->getMoodStats($couple, $days);

        // Get available moods
        $availableMoods = DailyMoodCheckIn::getAvailableMoods();

        // Check if current user has checked in today
        $myTodayMood = $todayMoods->firstWhere('user_id', $user->id);
        $hasCheckedIn = $myTodayMood !== null;

        return view('mood.index', compact(
            'couple',
            'todayMoods',
            'myTodayMood',
            'moodHistory',
            'moodStats',
            'availableMoods',
            'hasCheckedIn',
            'days'
        ));
    }

    /**
     * Handle mood check-in form submission.
     */
    public function checkIn(CreateMoodCheckInRequest $request)
    {
        try {
            $this->service->checkIn(
                Auth::user(),
                $request->mood,
                $request->note
            );

            return redirect()->route('mood.index')
                ->with('success', 'Mood check-in berhasil!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal check-in: ' . $e->getMessage());
        }
    }

    /**
     * Update today's mood check-in.
     */
    public function update(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:happy,sad,angry,loved,tired,anxious,excited',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();
            $mood = $this->service->findMoodForUser((int) $request->mood_id, $user->id);

            if (!$mood) {
                return back()->with('error', 'Mood check-in tidak ditemukan');
            }

            $this->service->updateMood($mood, $request->mood, $request->note);

            return redirect()->route('mood.index')
                ->with('success', 'Mood berhasil diupdate!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal update mood: ' . $e->getMessage());
        }
    }

    /**
     * Get mood statistics (AJAX endpoint).
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $days = (int) $request->get('days', 30);
        $stats = $this->service->getMoodStats($couple, $days);

        // Add emoji mapping and percentage
        $moodEmojis = DailyMoodCheckIn::getAvailableMoods();
        $statsWithEmoji = [];
        $total = $stats['total'] ?? 1; // Avoid division by zero

        foreach ($stats as $mood => $count) {
            if ($mood !== 'total' && $count > 0) {
                $statsWithEmoji[] = [
                    'mood' => $mood,
                    'emoji' => $moodEmojis[$mood] ?? '',
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        // Sort by count descending
        usort($statsWithEmoji, fn($a, $b) => $b['count'] - $a['count']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $statsWithEmoji,
                'total' => $stats['total'],
                'days' => $days,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMoodCheckInRequest;
use App\Http\Resources\DailyMoodCheckInResource;
use App\Models\DailyMoodCheckIn;
use App\Services\DailyMoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyMoodApiController extends Controller
{
    protected DailyMoodService $service;

    public function __construct(DailyMoodService $service)
    {
        $this->service = $service;
    }

    /**
     * Get mood history for the current user's couple.
     */
    public function index(Request $request): JsonResponse
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
        $moods = $this->service->getMoodHistory($couple, $days);

        return response()->json([
            'success' => true,
            'data' => DailyMoodCheckInResource::collection($moods),
        ]);
    }

    /**
     * Create a new mood check-in.
     */
    public function store(CreateMoodCheckInRequest $request): JsonResponse
    {
        try {
            $mood = $this->service->checkIn(
                Auth::user(),
                $request->mood,
                $request->note
            );

            return response()->json([
                'success' => true,
                'message' => 'Mood check-in berhasil!',
                'data' => DailyMoodCheckInResource::make($mood),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get today's mood for the couple.
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $moods = $this->service->getTodayMoods($couple);

        return response()->json([
            'success' => true,
            'data' => DailyMoodCheckInResource::collection($moods),
            'has_checked_in' => $this->service->hasCheckedInToday($user->id),
        ]);
    }

    /**
     * Get mood statistics.
     */
    public function stats(Request $request): JsonResponse
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

        // Add emoji mapping
        $moodEmojis = DailyMoodCheckIn::getAvailableMoods();
        $statsWithEmoji = [];
        foreach ($stats as $mood => $count) {
            if ($mood !== 'total') {
                $statsWithEmoji[] = [
                    'mood' => $mood,
                    'emoji' => $moodEmojis[$mood] ?? '',
                    'count' => $count,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $statsWithEmoji,
                'total' => $stats['total'],
                'days' => $days,
            ],
        ]);
    }

    /**
     * Update mood check-in.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'mood' => 'required|in:happy,sad,angry,loved,tired,anxious,excited',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $mood = $this->service->findMoodForUser($id, Auth::id());

            if (!$mood) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mood check-in tidak ditemukan',
                ], 404);
            }

            $updated = $this->service->updateMood($mood, $request->mood, $request->note);

            return response()->json([
                'success' => true,
                'message' => 'Mood berhasil diupdate!',
                'data' => DailyMoodCheckInResource::make($updated),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete mood check-in.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $mood = $this->service->findMoodForUser($id, Auth::id());

            if (!$mood) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mood check-in tidak ditemukan',
                ], 404);
            }

            $this->service->deleteMood($mood);

            return response()->json([
                'success' => true,
                'message' => 'Mood check-in berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\MissingYouService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MissingYouController extends Controller
{
    protected MissingYouService $service;

    public function __construct(MissingYouService $service)
    {
        $this->service = $service;
    }

    /**
     * Get "Missing You" history for the couple.
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

        $limit = (int) $request->get('limit', 20);
        $history = $this->service->getMissingYouHistory($couple, $limit);

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($history),
        ]);
    }

    /**
     * Send a "Missing You" notification to partner.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check rate limit
        if (!$this->service->canSendMissingYou($user)) {
            $secondsRemaining = $this->service->getTimeUntilNextAvailable($user);
            $minutesRemaining = ceil($secondsRemaining / 60);

            return response()->json([
                'success' => false,
                'message' => "Tunggu sekitar {$minutesRemaining} menit sebelum mengirim lagi",
                'data' => [
                    'can_send' => false,
                    'seconds_remaining' => $secondsRemaining,
                    'remaining_quota' => 0,
                ],
            ], 429);
        }

        $request->validate([
            'message' => 'nullable|string|max:200',
        ]);

        try {
            $notification = $this->service->sendMissingYou(
                $user,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => 'Missing You berhasil dikirim! 💕',
                'data' => NotificationResource::make($notification),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get "Missing You" quota/status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        $remainingQuota = $this->service->getRemainingQuota($user);
        $secondsRemaining = $this->service->getTimeUntilNextAvailable($user);

        return response()->json([
            'success' => true,
            'data' => [
                'can_send' => $remainingQuota > 0,
                'remaining_quota' => $remainingQuota,
                'max_quota' => 3,
                'seconds_remaining' => $secondsRemaining,
                'quota_resets_in' => $secondsRemaining ? ceil($secondsRemaining / 60) . ' minutes' : '0 minutes',
            ],
        ]);
    }

    /**
     * Get available message templates.
     */
    public function templates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'templates' => $this->service->getMessageTemplates(),
            ],
        ]);
    }
}

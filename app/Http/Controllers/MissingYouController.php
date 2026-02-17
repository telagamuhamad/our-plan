<?php

namespace App\Http\Controllers;

use App\Services\MissingYouService;
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
     * Display Missing You page with history and quick action button.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum memiliki pasangan aktif');
        }

        $limit = (int) $request->get('limit', 20);
        $history = $this->service->getMissingYouHistory($couple, $limit);
        $remainingQuota = $this->service->getRemainingQuota($user);
        $secondsRemaining = $this->service->getTimeUntilNextAvailable($user);

        return view('missing-you.index', compact(
            'couple',
            'history',
            'remainingQuota',
            'secondsRemaining',
            'limit'
        ));
    }

    /**
     * Send a Missing You notification (web form submission).
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:200',
        ]);

        $user = Auth::user();

        // Check rate limit
        if (!$this->service->canSendMissingYou($user)) {
            return back()
                ->with('error', 'Tunggu sebentar sebelum mengirim lagi. Rate limit: 3x per jam');
        }

        try {
            $this->service->sendMissingYou($user, $request->message);

            return back()->with('success', 'Missing You berhasil dikirim! 💕');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim: ' . $e->getMessage());
        }
    }

    /**
     * Get status (AJAX endpoint for quota checking).
     */
    public function status(Request $request)
    {
        $user = Auth::user();

        if (!$user->couple || !$user->couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $remainingQuota = $this->service->getRemainingQuota($user);
        $secondsRemaining = $this->service->getTimeUntilNextAvailable($user);

        return response()->json([
            'success' => true,
            'data' => [
                'can_send' => $remainingQuota > 0,
                'remaining_quota' => $remainingQuota,
                'max_quota' => 3,
                'seconds_remaining' => $secondsRemaining,
            ],
        ]);
    }

    /**
     * Get message templates (AJAX endpoint).
     */
    public function templates(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'templates' => $this->service->getMessageTemplates(),
            ],
        ]);
    }
}

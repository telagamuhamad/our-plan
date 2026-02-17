<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptPairingRequest;
use App\Http\Requests\CreateInviteCodeRequest;
use App\Http\Requests\JoinCoupleRequest;
use App\Http\Resources\CoupleResource;
use App\Http\Resources\UserResource;
use App\Services\PairingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoupleApiController extends Controller
{
    protected PairingService $pairingService;

    public function __construct(PairingService $pairingService)
    {
        $this->pairingService = $pairingService;
    }

    /**
     * Create a new invite code for the user.
     */
    public function createInviteCode(CreateInviteCodeRequest $request)
    {
        try {
            $couple = $this->pairingService->createInviteCode(Auth::user());
            $couple->load('userOne');

            return response()->json([
                'success' => true,
                'message' => 'Kode undangan berhasil dibuat.',
                'data' => new CoupleResource($couple),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Join a couple using invite code.
     */
    public function joinCouple(JoinCoupleRequest $request)
    {
        try {
            $couple = $this->pairingService->joinCouple(
                $request->invite_code,
                Auth::user()
            );
            $couple->load(['userOne', 'userTwo']);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil bergabung! Tunggu konfirmasi dari pasangan.',
                'data' => new CoupleResource($couple),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get the current user's couple status.
     */
    public function getStatus(Request $request)
    {
        $coupleInfo = $this->pairingService->getCoupleInfo(Auth::user());

        if (!$coupleInfo) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_couple' => false,
                    'message' => 'Anda belum terhubung dengan pasangan.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($coupleInfo, [
                'has_couple' => true,
                'partner' => $coupleInfo['partner'] ? new UserResource($coupleInfo['partner']) : null,
            ]),
        ], 200);
    }

    /**
     * Confirm pairing.
     */
    public function confirmPairing(AcceptPairingRequest $request)
    {
        try {
            $couple = $this->pairingService->confirmPairing(
                $request->couple_id,
                Auth::user()
            );
            $couple->load(['userOne', 'userTwo']);

            return response()->json([
                'success' => true,
                'message' => 'Pairing berhasil! Selamat datang.',
                'data' => new CoupleResource($couple),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Leave the current couple.
     */
    public function leaveCouple(Request $request)
    {
        try {
            $this->pairingService->leaveCouple(Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Pasangan telah dihapus. Kedua user sekarang sudah terunpair.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get couple details.
     */
    public function show(Request $request)
    {
        $couple = $this->pairingService->getCoupleForUser(Auth::user());

        if (!$couple) {
            return response()->json([
                'success' => false,
                'message' => 'Pasangan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CoupleResource($couple->load(['userOne', 'userTwo'])),
        ], 200);
    }
}

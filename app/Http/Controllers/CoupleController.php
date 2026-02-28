<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptPairingRequest;
use App\Http\Requests\CreateInviteCodeRequest;
use App\Http\Requests\JoinCoupleRequest;
use App\Services\PairingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoupleController extends Controller
{
    protected PairingService $pairingService;

    public function __construct(PairingService $pairingService)
    {
        $this->pairingService = $pairingService;
    }

    /**
     * Show the create invite page.
     */
    public function showCreateInvite()
    {
        $user = Auth::user();

        if ($user->hasActiveCouple()) {
            return redirect()->route('dashboard');
        }

        return view('pairing.create-invite', compact('user'));
    }

    /**
     * Store a new invite code.
     */
    public function storeInviteCode(CreateInviteCodeRequest $request)
    {
        try {
            $user = Auth::user();
            $couple = $this->pairingService->createInviteCode($user);

            // Re-login user to update session with fresh data
            Auth::login($user->fresh());

            return redirect()->route('pairing.status')
                ->with('success', 'Kode undangan berhasil dibuat.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the join couple page.
     */
    public function showJoin()
    {
        $user = Auth::user();

        if ($user->hasActiveCouple()) {
            return redirect()->route('dashboard');
        }

        return view('pairing.join', compact('user'));
    }

    /**
     * Join a couple using invite code.
     */
    public function join(JoinCoupleRequest $request)
    {
        try {
            $user = Auth::user();
            $couple = $this->pairingService->joinCouple(
                $request->invite_code,
                $user
            );

            // Re-login user to update session with fresh data
            Auth::login($user->fresh());

            return redirect()->route('pairing.status')
                ->with('success', 'Berhasil bergabung! Tunggu konfirmasi dari pasangan.');
        } catch (Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the pairing status page.
     */
    public function showStatus()
    {
        $user = Auth::user();

        // If user has active couple, redirect to dashboard
        if ($user->hasActiveCouple()) {
            return redirect()->route('dashboard');
        }

        $coupleInfo = $this->pairingService->getCoupleInfo($user);

        return view('pairing.status', [
            'user' => $user,
            'coupleInfo' => $coupleInfo,
        ]);
    }

    /**
     * Confirm the pairing.
     */
    public function confirm(AcceptPairingRequest $request)
    {
        try {
            $couple = $this->pairingService->confirmPairing(
                $request->couple_id,
                Auth::user()
            );

            return redirect()->route('dashboard')
                ->with('success', 'Pairing berhasil! Selamat datang di Dashboard!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Leave the current couple.
     */
    public function leave(Request $request)
    {
        $user = Auth::user();

        if (!$user->couple_id) {
            return back()->with('error', 'Anda tidak terhubung dengan pasangan manapun.');
        }

        try {
            $this->pairingService->leaveCouple($user);

            return redirect()->route('dashboard')
                ->with('success', 'Pasangan telah dihapus. Kedua user sekarang sudah terunpair.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal keluar dari pasangan.');
        }
    }
}

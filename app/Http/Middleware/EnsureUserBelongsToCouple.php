<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToCouple
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->couple_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus terhubung dengan pasangan terlebih dahulu.',
                ], 403);
            }

            return redirect()->route('pairing.status')
                ->with('error', 'Anda harus terhubung dengan pasangan terlebih dahulu.');
        }

        // Check couple ownership for specific routes with coupleId parameter
        $coupleId = $request->route('coupleId') ?? $request->route('couple')?->id ?? $request->input('couple_id');

        if ($coupleId && $user->couple_id != $coupleId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke data pasangan ini.',
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke data pasangan ini.');
        }

        return $next($request);
    }
}

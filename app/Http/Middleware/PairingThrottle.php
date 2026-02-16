<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PairingThrottle
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = Auth::id() ?? $request->ip();
        return sha1('pairing:' . $userId . ':' . $request->ip() . ':' . $request->route()->getName());
    }

    /**
     * Create a response for rate limit exceeded.
     */
    protected function buildResponse(string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $seconds = $this->limiter->availableIn($key);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam ' . $this->getTimeUntilReady($seconds) . '.',
            ], 429);
        }

        return back()
            ->with('error', 'Terlalu banyak percobaan. Silakan coba lagi dalam ' . $this->getTimeUntilReady($seconds) . '.');
    }

    /**
     * Get time until ready in human readable format.
     */
    protected function getTimeUntilReady(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        return ceil($seconds / 60) . ' menit';
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRequestTimestamp
{
    /**
     * Require that every API request provides a `timestamp` either as header `X-Timestamp` or input.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to API routes
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $ts = $request->header('X-Timestamp') ?? $request->input('timestamp');

        if (empty($ts) || ! is_numeric($ts)) {
            return response()->json([
                'status' => 'error',
                'timestamp' => now()->toIso8601String(),
                'error' => 'Missing or invalid timestamp. Include unix timestamp in header X-Timestamp or body param timestamp.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Optionally: you could check clock skew here and reject too-old requests
        return $next($request);
    }
}

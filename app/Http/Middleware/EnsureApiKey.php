<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $provided = $request->header('X-API-KEY');
        $expected = config('services.api.key');

        if (empty($expected)) {
            return $next($request);
        }

        if (! hash_equals((string)$expected, (string)$provided)) {
            return response()->json([
                'status' => 'error',
                'timestamp' => now()->toIso8601String(),
                'error' => 'Invalid or missing API key.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

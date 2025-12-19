<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseWrapper
{
    /**
     * Wrap JSON responses with { status, timestamp, data|error }
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only wrap API JSON responses
        if (! $request->is('api/*')) {
            return $response;
        }

        // If response is already a JsonResponse, decode it
        if ($response instanceof JsonResponse) {
            $original = $response->getData(true);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                $payload = [
                    'status' => 'error',
                    'timestamp' => now()->toIso8601String(),
                    'error' => $original,
                ];
                return response()->json($payload, $statusCode);
            }

            $payload = [
                'status' => 'success',
                'timestamp' => now()->toIso8601String(),
                'data' => $original,
            ];

            return response()->json($payload, $statusCode);
        }

        // For non-JSON responses (views, etc.) return as-is
        return $response;
    }
}

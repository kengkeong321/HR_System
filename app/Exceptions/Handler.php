<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // default
    }

    public function render($request, Throwable $e)
    {
        // If this is an API request, return a well-formed JSON payload with status and timestamp
        if ($request->is('api/*') || $request->expectsJson()) {
            $status = 500;
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            }

            $message = config('app.debug') ? $e->getMessage() : 'Server Error';

            $payload = [
                'status' => 'error',
                'timestamp' => now()->toIso8601String(),
                'error' => [
                    'message' => $message,
                    'code' => $status,
                ],
            ];

            return response()->json($payload, $status);
        }

        return parent::render($request, $e);
    }
}

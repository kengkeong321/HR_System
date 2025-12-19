<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Return all active positions formatted for dropdowns.
     *
     * Response format:
     * {
     *   status: 'ok',
     *   timestamp: 1234567890,
     *   data: [ { position_id, name }, ... ]
     * }
     */
    public function index(Request $request)
    {
        $positions = Position::where('status', 'Active')
            ->orderBy('name')
            ->get(['position_id', 'name']);

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'data' => $positions->map(fn($p) => ['position_id' => $p->position_id, 'name' => $p->name]),
        ]);
    }
}

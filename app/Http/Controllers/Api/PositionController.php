<?php
//Woo Keng Keong
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    // return list of active positions as JSON
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

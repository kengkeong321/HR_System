<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use Illuminate\Http\Request;

Route::get('/attendance/summary', function (Request $request) {
    
    // Mandatory Requirement [85, 86]: Return status and timestamp
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'data' => Attendance::all() 
    ]);
});
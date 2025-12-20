<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/attendance/summary', function (Request $request) {
    
    // Mandatory Requirement [85, 86]: Return status and timestamp
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'data' => Attendance::all() 
    ]);
});

Route::get('/attendance', function (Request $request) {
    $userId = $request->query('user_id');
    $month = $request->query('month');
    $year = $request->query('year');

    // Fetch the raw hours from the database based on your screenshot
    $totalHours = DB::table('attendances')
        ->where('user_id', $userId)
        ->whereYear('attendance_date', $year)
        ->whereMonth('attendance_date', $month)
        ->where('status', 'Present')
        ->whereNotNull('clock_out_time')
        ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)) / 3600 as hours')
        ->value('hours') ?? 0;

    // Fulfilling the requirement for structured data exchange
    return response()->json([
        'status' => 'ok',                               // Status indicator
        'timestamp' => now()->toDateTimeString(),       // Current timestamp
        'data' => [
            'user_id' => $userId,
            'total_hours' => round($totalHours, 4),     // Raw data
            'employment_type' => 'Part-Time'
        ]
    ]);
});
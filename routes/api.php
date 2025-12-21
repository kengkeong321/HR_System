<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Staff;

Route::get('/attendance/summary', function (Request $request) {
    
    // Join the 'user' and 'staff' tables with 'attendances'
    $summaryData = DB::table('attendances')
        ->join('user', 'attendances.user_id', '=', 'user.user_id')
        ->join('staff', 'user.user_id', '=', 'staff.user_id')
        ->select(
            'attendances.id as attendance_id',
            'user.user_name',
            'staff.employment_type',
            'attendances.attendance_date',
            'attendances.status',
            'attendances.clock_in_time',
            'attendances.clock_out_time'
        )
        ->get();

    // Mandatory Requirement: Return status and timestamp
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'data' => $summaryData 
    ]);
});

Route::get('/attendance', function (Illuminate\Http\Request $request) {
    $userId = $request->query('user_id');
    $month = $request->query('month');
    $year = $request->query('year');

    // Use 'user' instead of 'users' to match your phpMyAdmin
    $userData = DB::table('user') 
        ->join('staff', 'user.user_id', '=', 'staff.user_id') 
        ->where('user.user_id', $userId)
        ->select('user.user_name', 'staff.employment_type') 
        ->first();

    // Calculate total hours...
    $totalHours = DB::table('attendances')
        ->where('user_id', $userId)
        ->whereYear('attendance_date', $year)
        ->whereMonth('attendance_date', $month)
        ->where('status', 'Present')
        ->whereNotNull('clock_out_time')
        ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)) / 3600 as hours')
        ->value('hours') ?? 0;

    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toDateTimeString(),
        'data' => [
            'user_id' => $userId,
            'user_name' => $userData->user_name ?? 'Unknown User', 
            'total_hours' => round($totalHours, 4),
            'employment_type' => $userData->employment_type ?? 'N/A'
        ]
    ]);
});

Route::get('/staff', function () {
    return response()->json([
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'data' => Staff::with('user')->get()
    ]);
});
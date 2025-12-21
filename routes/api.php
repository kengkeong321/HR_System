<?php
//Mu Jun Yi & Dephnie Ong Yan Yee
use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Staff;
use App\Business\Services\PayrollService;


Route::get('/staff', function () {
    return response()->json([
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'data' => Staff::with('user')->get()
    ]);
});


//Mu Jun Yi
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


//Dephnie Ong Yan Yee
Route::get('/attendance-summary', function (Request $request, PayrollService $service) {
    $userId = $request->query('user_id');
    $month = $request->query('month');
    $year = $request->query('year');

    $userData = DB::table('user')
        ->join('staff', 'user.user_id', '=', 'staff.user_id')
        ->where('user.user_id', $userId)
        ->select('user.user_name', 'staff.employment_type')
        ->first();

    $totalHours = $service->calculateTotalWorkedHours($userId, $month, $year);

    return response()->json([
        'status' => 'ok',
        'data' => [
            'user_id' => $userId,
            'user_name' => $userData->user_name ?? 'Unknown User',
            'total_hours' => round($totalHours, 2),
            'employment_type' => $userData->employment_type ?? 'N/A'
        ]
    ]);
});

<?php

namespace App\Observers;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AttendanceObserver
{
    public function created(Attendance $attendance)
    {
        // 1. Fetch work start time from settings
        $startTimeSetting = DB::table('settings')
            ->where('key_name', 'work_start_time')
            ->value('key_value') ?? '09:00:00';

        // 2. Compare Subject's data to Policy Information
        if (strtotime($attendance->clock_in_time) > strtotime($startTimeSetting)) {
            // 3. Update state automatically (Observer Pattern)
            $attendance->status = 'Late';
            $attendance->remarks = 'Auto-marked: Late arrival';
            $attendance->save();
        }
    }
}
<?php
//Mu Jun Yi
namespace App\Observers;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AttendanceObserver
{
    public function created(Attendance $attendance)
    {
        $startTimeSetting = DB::table('settings')
            ->where('key_name', 'work_start_time')
            ->value('key_value') ?? '09:00:00';

        if (strtotime($attendance->clock_in_time) > strtotime($startTimeSetting)) {
            $attendance->status = 'Late';
            $attendance->remarks = 'Auto-marked: Late arrival';
            $attendance->save();
        }
    }
}
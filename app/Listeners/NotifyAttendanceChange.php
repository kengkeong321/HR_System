<?php

namespace App\Listeners;

use App\Events\AttendanceMarked;
use Illuminate\Support\Facades\Log;

class NotifyAttendanceChange
{
    public function handle(AttendanceMarked $event)
    {
        // This logic runs automatically when attendance is marked
        Log::info('Observer Notified: Attendance recorded for User ID ' . $event->attendance->user_id);
    }
}
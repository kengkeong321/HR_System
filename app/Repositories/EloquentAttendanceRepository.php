<?php

namespace App\Repositories;

use App\Models\Attendance;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function create(array $data)
    {
        return Attendance::create($data);
    }

    public function getByUserId($userId)
    {
        return Attendance::where('user_id', $userId)->get();
    }
}
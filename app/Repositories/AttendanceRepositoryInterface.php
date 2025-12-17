<?php

namespace App\Repositories;

interface AttendanceRepositoryInterface
{
    public function create(array $data);
    public function getByUserId($userId);
}
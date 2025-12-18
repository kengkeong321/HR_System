<?php

namespace App\Business\Strategies\Contracts;

use App\Models\Staff;

interface SalaryComponentInterface
{
    public function calculate(Staff $staff, array $data): float;
}
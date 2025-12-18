<?php

namespace App\Business\Strategies;

use App\Business\Strategies\Contracts\SalaryComponentInterface;
use App\Models\Staff;

class EPFStrategy implements SalaryComponentInterface
{
    public function calculate(Staff $staff, array $data): float
    {
        // Configuration is pulled from config files, not hardcoded (Separation 4)
        $rate = config('payroll.epf_rate', 0.11); 
        
        return $staff->base_salary * $rate;
    }
}
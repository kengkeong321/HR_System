<?php

namespace App\Business\Strategies;

use App\Business\Strategies\Contracts\SalaryComponentInterface;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class EPFStrategy implements SalaryComponentInterface
{
    public function calculate(Staff $staff, array $data): float
    {
        $salary = (float) ($data['basic_salary'] ?? 0);
        $type   = $data['type'] ?? 'employee';

        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        if ($type === 'employee') {
            $rate = ($configs['staff_epf_rate'] ?? 11.0) / 100;
            return round($salary * $rate, 2);
        }

        $threshold = 5000;
        $rateKey = ($salary <= $threshold) ? 'employer_epf_rate_low' : 'employer_epf_rate_high';
        $rate = ($configs[$rateKey] ?? 13.0) / 100;
        
        return round($salary * $rate, 2);
    }
}
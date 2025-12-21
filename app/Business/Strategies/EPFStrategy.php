<?php
//Dephnie Ong Yan Yee

namespace App\Business\Strategies;

use App\Business\Strategies\Contracts\SalaryComponentInterface;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class EPFStrategy implements SalaryComponentInterface
{
    public function calculate(Staff $staff, array $data): array
    {
        $salary = (float) ($data['calculated_basic'] ?? $staff->basic_salary);

        if ($salary <= 0) {
            return [
                'epf_employee_rm' => 0,
                'epf_employer_rm' => 0,
                'epf_employee_percent' => 0,
                'epf_employer_percent' => 0
            ];
        }

        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        $employeeRatePercent = (float) ($configs['staff_epf_rate'] ?? 11.0);
        
        $threshold = (float) ($configs['employer_epf_threshold'] ?? 5000.0);
        $rateKey = ($salary <= $threshold) ? 'employer_epf_rate_low' : 'employer_epf_rate_high';
        $employerRatePercent = (float) ($configs[$rateKey] ?? ($salary <= $threshold ? 13.0 : 12.0));

        $employeeRM = ceil($salary * ($employeeRatePercent / 100)); 
        $employerRM = ceil($salary * ($employerRatePercent / 100));

        return [
            'epf_employee_rm' => (float) $employeeRM,
            'epf_employer_rm' => (float) $employerRM,
            'epf_employee_percent' => $employeeRatePercent,
            'epf_employer_percent' => $employerRatePercent,
            'note' => "Calculated via Percentage (Table Missing)"
        ];
    }
}
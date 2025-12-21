<?php
//Dephnie Ong Yan Yee

namespace App\Business\Strategies;

use App\Business\Strategies\Contracts\SalaryComponentInterface;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class SocsoTableStrategy implements SalaryComponentInterface
{
    public function calculate(Staff $staff, array $data): array
    {
        $salary = (float) ($data['calculated_basic'] ?? $staff->basic_salary);

        if ($staff->employment_type === 'Casual') {
            return [
                'socso_employee_rm' => 0,
                'socso_employer_rm' => 0,
                'eis_employee_rm' => 0,
                'eis_employer_rm' => 0
            ];
        }

        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
        $wageCeiling = (float) ($configs['socso_ceiling'] ?? 6000.0);
        $salaryForCalculation = min($salary, $wageCeiling);

        // SOCSO 
        $rateRecord = DB::table('socso_rates')
            ->where('min_salary', '<=', $salaryForCalculation)
            ->where(function ($query) use ($salaryForCalculation) {
                $query->where('max_salary', '>=', $salaryForCalculation)
                    ->orWhereNull('max_salary');
            })
            ->first();

        // EIS Calculation 
        $eisRate = 0.002;
        $eisEmployee = round($salaryForCalculation * $eisRate, 2);
        $eisEmployer = round($salaryForCalculation * $eisRate, 2);

        if (!$rateRecord) {
            return [
                'socso_employee_rm' => 0,
                'socso_employer_rm' => 0,
                'eis_employee_rm' => $eisEmployee,
                'eis_employer_rm' => $eisEmployer
            ];
        }

        return [
            'socso_employee_rm' => (float) $rateRecord->employee_share,
            'socso_employer_rm' => (float) $rateRecord->employer_share,
            'eis_employee_rm' => $eisEmployee,
            'eis_employer_rm' => $eisEmployer
        ];
    }
}

<?php

namespace App\Business\Strategies;

use App\Business\Strategies\Contracts\SalaryComponentInterface;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class SocsoTableStrategy implements SalaryComponentInterface 
{
    public function calculate(Staff $staff, array $data): float
    {
        $salary = (float) ($data['basic_salary'] ?? 0);
        $type   = $data['type'] ?? 'employee';

        $salaryForCalculation = $salary;

        $rateRecord = DB::table('socso_rates')
            ->where('min_salary', '<=', $salaryForCalculation)
            ->where(function ($query) use ($salaryForCalculation) {
                $query->where('max_salary', '>=', $salaryForCalculation)
                    ->orWhereNull('max_salary');
            })
            ->first();

        if (!$rateRecord) {
            return 0.00;
        }

        if ($type === 'employee') {
            return (float) $rateRecord->employee_share;
        }

        return (float) $rateRecord->employer_share;
    }
}

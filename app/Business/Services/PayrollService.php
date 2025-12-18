<?php

namespace App\Business\Services;

use App\Models\Staff;
use App\Models\Payroll; // Use the correct Model name
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected $strategies = [];

    // Inject strategies via constructor or a registry
    public function registerComponent(string $name, $strategy)
    {
        $this->strategies[$name] = $strategy;
    }

    public function generatePayroll(Staff $staff, $month, $year, array $options = [])
    {
        // 1. Calculate Base Salary
        // (You can expand this later for Unpaid Leave logic)
        $basicSalary = $staff->basic_salary;

        // 2. Calculate Strategies (EPF, SOCSO, Tax, etc.)
        $totalDeductions = 0;
        $breakdown = [];

        foreach ($this->strategies as $name => $strategy) {
            $amount = $strategy->calculate($staff, ['month' => $month, 'year' => $year, 'gross' => $basicSalary]);
            $breakdown[$name] = $amount;
            $totalDeductions += $amount;
        }

        // 3. Calculate Net Salary
        // (If you have allowances, fetch and add them here)
        $totalAllowances = 0; // Placeholder: Add logic if you have an Allowance Strategy
        $netSalary = ($basicSalary + $totalAllowances) - $totalDeductions;
        // 4. Save to Database
        return Payroll::updateOrCreate(
            [
                'staff_id' => $staff->staff_id,
                'month'    => $month,
                'year'     => $year,
            ],
            [
                'batch_id'     => $options['batch_id'] ?? null,
                'basic_salary' => $basicSalary,
                'allowances'   => $totalAllowances,
                'deduction'    => $totalDeductions,
                'net_salary'   => $netSalary,
                'status'       => 'Draft',
                'allowance_remark' => $options['remark'] ?? null,
                'breakdown'    => json_encode($breakdown),
                'updated_at'   => now(),
            ]
        );
    }

    /**
     * Helper: Generate a Batch for All Active Staff (Optional Wrapper)
     */
    public function generateBatchForMonth($month, $year)
    {
        // This is just a helper if you want to move the loop out of the Controller
        // But your current Controller handles the loop fine.
    }
}

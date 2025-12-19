<?php

namespace App\Business\Services;

use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Claim;
use App\Models\PayrollBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Business\Strategies\EPFStrategy;
use App\Business\Strategies\SocsoTableStrategy;
use App\Business\Strategies\Contracts\SalaryComponentInterface;
use Illuminate\Support\Facades\Http;

class PayrollService
{
    protected $strategies = [];

    public function registerComponent(string $key, SalaryComponentInterface $strategy)
    {
        $this->strategies[$key] = $strategy;
    }

    public function __construct()
    {
        // These strategies handle the complex Malaysian statutory tables
        $this->strategies['epf'] = new EPFStrategy();
        $this->strategies['socso'] = new SocsoTableStrategy();
    }

    public function getApprovedClaimsTotal($staffId, $month, $year)
    {
        return Claim::where('staff_id', $staffId)
            ->where('status', 'Approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');
    }
    

    public function calculateAndSavePayroll(Staff $staff, int $month, int $year, int $batchId, array $manualInputs = [])
{
    // 1. Fetch Dynamic Configuration from payroll_configs table
    $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
    
    $staffEpfRate = (float) ($configs['staff_epf_rate'] ?? 11.0) / 100;
    $eisRate = (float) ($configs['eis_rate'] ?? 0.2) / 100;
    $socsoCeiling = (float) ($configs['socso_ceiling'] ?? 6000.0);

  $isPartTime = ($staff->employment_type === 'Part-Time');
    $hourlyRate = 50.00; 

    if ($isPartTime) {
        // Step 1: Direct DB Query to sum hours for User 6
        $totalHours = DB::table('attendances')
            ->where('user_id', $staff->user_id) // Ensures we target User 6
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereIn('status', ['Present', 'Late']) // Include Late to be safe
            ->whereNotNull('clock_out_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)) / 3600 as total_hours')
            ->value('total_hours') ?? 0;

        // Step 2: Calculate Basic Salary
        $basic = $totalHours * $hourlyRate;
    } else {
        $basic = (float) ($manualInputs['basic_salary'] ?? $staff->basic_salary);
    }

    // 3. Process Allowances and Manual Deductions
    $totalAllowances = (float) ($manualInputs['total_allowances'] ?? 0);
    $manualDeduction = (float) ($manualInputs['manual_deduction'] ?? 0);

    // 4. Dynamic Statutory Calculations
    if ($basic > 0) {
        // Employee EPF (e.g., 12% of basic)
        $epfEmployee = $basic * $staffEpfRate;
        
        // SOCSO/EIS capped by dynamic ceiling
        $salaryForStatutory = min($basic, $socsoCeiling);
        
        $socsoEmployee = $this->strategies['socso']->calculate($staff, ['basic_salary' => $salaryForStatutory, 'type' => 'employee']);
        $eisEmployee = $salaryForStatutory * $eisRate;

        // Employer Portions
        $threshold = (float) ($configs['employer_epf_threshold'] ?? 5000.0);
        $employerRateKey = $basic <= $threshold ? 'employer_epf_rate_low' : 'employer_epf_rate_high';
        $employerEpfRate = (float) ($configs[$employerRateKey] ?? 13.0) / 100;
        
        $epfEmployer = $basic * $employerEpfRate;
        $socsoEmployer = $this->strategies['socso']->calculate($staff, ['basic_salary' => $salaryForStatutory, 'type' => 'employer']);
        $eisEmployer = $salaryForStatutory * $eisRate;
    } else {
        $epfEmployee = $epfEmployer = $socsoEmployee = $socsoEmployer = $eisEmployee = $eisEmployer = 0;
    }

    // 5. Final Calculations
    $totalDeductions = $epfEmployee + $socsoEmployee + $eisEmployee + $manualDeduction;
    $netSalary = ($basic + $totalAllowances) - $totalDeductions;

    // 6. Save to Payrolls Table
    return Payroll::updateOrCreate(
        ['staff_id' => $staff->staff_id, 'month' => $month, 'year' => $year],
        [
            'batch_id' => $batchId,
            'basic_salary' => round($basic, 2),
            'allowances' => round($totalAllowances, 2),
            'deduction' => round($totalDeductions, 2),
            'manual_deduction' => round($manualDeduction, 2),
            'net_salary' => round($netSalary, 2),
            'status' => 'Draft',
            'breakdown' => [
                'calculated_amounts' => [
                    'hours_worked' => $isPartTime ? round($totalHours, 2) : null,
                    'hourly_rate' => $isPartTime ? $hourlyRate : null,
                    'epf_employee_rm' => round($epfEmployee, 2),
                    'epf_employer_rm' => round($epfEmployer, 2),
                    'socso_employee_rm' => round($socsoEmployee, 2),
                    'socso_employer_rm' => round($socsoEmployer, 2),
                    'eis_employee_rm' => round($eisEmployee, 2),
                    'eis_employer_rm' => round($eisEmployer, 2),
                ]
            ],
            'allowance_remark' => $manualInputs['allowance_remark'] ?? ($isPartTime ? "API Sync: " . round($totalHours, 2) . " hrs @ RM50" : null),
            'deduction_remark' => $manualInputs['deduction_remark'] ?? null,
        ]
    );
}
   public function updateBatchTotals($batchId)
    {
        $freshTotals = Payroll::where('batch_id', $batchId)
            ->selectRaw('SUM(net_salary) as total_disbursement, COUNT(*) as count')
            ->first();

        PayrollBatch::where('id', $batchId)->update([
            'total_amount' => round($freshTotals->total_disbursement, 2),
            'total_staff' => $freshTotals->count,
        ]);
    }

    /**
     * Helper: Calculate OT from attendance records
     * Currently unused but available for future implementation
     */
    private function calculateAttendanceOt(Staff $staff, $month, $year)
    {
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
        $standardDailyHours = $configs['standard_work_hours'] ?? 9;
        $breakTimeHours = $configs['break_time_hours'] ?? 1;

        $attendances = \App\Models\Attendance::where('user_id', $staff->user_id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNotNull('clock_out_time')
            ->whereNotNull('clock_in_time')
            ->get();

        $totalOtHours = 0;

        foreach ($attendances as $record) {
            $clockIn = \Carbon\Carbon::parse($record->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($record->clock_out_time);

            $hoursWorked = $clockOut->floatDiffInHours($clockIn);

            // Deduct break time if applicable
            if ($staff->auto_deduct_break && $hoursWorked > 5) {
                $hoursWorked -= $breakTimeHours;
            }

            // Calculate OT
            if ($hoursWorked > $standardDailyHours) {
                $totalOtHours += ($hoursWorked - $standardDailyHours);
            }
        }

        return $totalOtHours;
    }
}

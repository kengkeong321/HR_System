<?php

namespace App\Business\Services;

use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Claim;
use App\Models\PayrollBatch;
use Illuminate\Support\Facades\DB;
use App\Business\Strategies\EPFStrategy;
use App\Business\Strategies\SocsoTableStrategy;
use App\Business\Strategies\Contracts\SalaryComponentInterface;

class PayrollService
{
    protected $strategies = [];

    public function registerComponent(string $key, SalaryComponentInterface $strategy)
    {
        $this->strategies[$key] = $strategy;
    }

    public function __construct()
    {
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
        // 1. Basic Salary & Attendance OT Integration
        $basic = (float) ($manualInputs['basic_salary'] ?? $staff->basic_salary);

        // [FIX] Call the previously unused method to get OT hours
        $otHours = $this->calculateAttendanceOt($staff, $month, $year);
        $hourlyRate = (float) ($staff->hourly_rate ?? 0);
        $calculatedAttendanceOtAmount = $otHours * $hourlyRate;

        // 2. Lecturer Extra Class Logic
        $extraClassAmount = 0;
        if (stripos($staff->position, 'Lecturer') !== false) {
            $extraClassAmount = DB::table('claims')
                ->where('staff_id', $staff->staff_id)
                ->where('claim_type', 'Extra Class')
                ->where('status', 'Approved')
                ->whereMonth('claim_date', $month)
                ->whereYear('claim_date', $year)
                ->sum('amount');
        }

        // 3. New Malaysian Statutory Ceiling (RM 6,000)
        $socsoEisCeiling = 6000;
        $salaryForStatutory = min($basic, $socsoEisCeiling);

        // [FIX] Correct Statutory Calculations
        if ($basic > 0) {
            $epfEmployee = $this->strategies['epf']->calculate($staff, ['basic_salary' => $basic, 'type' => 'employee']);
            $epfEmployer = $this->strategies['epf']->calculate($staff, ['basic_salary' => $basic, 'type' => 'employer']);

            // SOCSO/EIS math using the 6000 ceiling
            $socsoEmployee = $this->strategies['socso']->calculate($staff, ['basic_salary' => $salaryForStatutory, 'type' => 'employee']);
            $socsoEmployer = $this->strategies['socso']->calculate($staff, ['basic_salary' => $salaryForStatutory, 'type' => 'employer']);

            $eisRate = (DB::table('payroll_configs')->where('config_key', 'eis_rate')->value('config_value') ?? 0.2) / 100;
            $eisEmployee = $salaryForStatutory * $eisRate;
            $eisEmployer = $salaryForStatutory * $eisRate;
        } else {
            $epfEmployee = $epfEmployer = $socsoEmployee = $socsoEmployer = $eisEmployee = $eisEmployer = 0;
        }

        // 4. Final Totals
        $generalClaims = $this->getApprovedClaimsTotal($staff->staff_id, $month, $year);

        // Allowances = Claims + Attendance OT + Lecturer Extra Class + Manual Adjustments
        $totalAllowances = $generalClaims + $calculatedAttendanceOtAmount + $extraClassAmount + ($manualInputs['total_allowances'] ?? 0);

        $manualDeductions = (float) ($manualInputs['total_deductions'] ?? 0);
        $totalDeductions = $epfEmployee + $socsoEmployee + $eisEmployee + $manualDeductions;

        $netSalary = ($basic + $totalAllowances) - $totalDeductions;

        // 5. Structure Breakdown and Save
        $breakdown = [
            'calculated_amounts' => [
                'epf_employee_rm' => round($epfEmployee, 2),
                'epf_employer_rm' => round($epfEmployer, 2),
                'socso_employee_rm' => round($socsoEmployee, 2),
                'socso_employer_rm' => round($socsoEmployer, 2),
                'eis_employee_rm' => round($eisEmployee, 2),
                'eis_employer_rm' => round($eisEmployer, 2),
                'attendance_ot_hours' => $otHours,
                'attendance_ot_rm' => round($calculatedAttendanceOtAmount, 2),
                'manual_deduction_rm' => round($manualDeductions, 2)
            ]
        ];

        return Payroll::updateOrCreate(
            ['staff_id' => $staff->staff_id, 'month' => $month, 'year' => $year],
            [
                'batch_id' => $batchId,
                'basic_salary' => $basic,
                'allowances' => $totalAllowances,
                'deduction' => $totalDeductions,
                'net_salary' => $netSalary,
                'status' => 'Draft',
                'breakdown' => $breakdown,
                'allowance_remark' => $manualInputs['allowance_remark'] ?? null,
                'deduction_remark' => $manualInputs['deduction_remark'] ?? null, // [FIX] Added missing column
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

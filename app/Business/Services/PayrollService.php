<?php
//Dephnie Ong Yan Yee

namespace App\Business\Services;

use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Claim;
use App\Models\PayrollBatch;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Business\Strategies\EPFStrategy;
use App\Business\Strategies\SocsoTableStrategy;
use App\Business\Strategies\Contracts\SalaryComponentInterface;
use Carbon\Carbon;

class PayrollService
{
    protected $strategies = [];

    public function __construct()
    {
        $this->strategies['epf'] = new EPFStrategy();
        $this->strategies['socso'] = new SocsoTableStrategy();
    }

    public function registerComponent(string $key, SalaryComponentInterface $strategy)
    {
        $this->strategies[$key] = $strategy;
    }

    /**
     * total approved claims (month)
     */
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
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
        $otStrategy = new \App\Business\Strategies\OvertimeStrategy();

        // basic salary
        if ($staff->employment_type === 'Part-Time') {
            $totalWorkedHours = $this->calculateTotalWorkedHours($staff->user_id, $month, $year);
            $basic = (float) ($totalWorkedHours * $staff->hourly_rate);
        } else {
            $basic = (float) ($manualInputs['basic_salary'] ?? $staff->basic_salary);
        }

        // ot
        $workDays = (float) ($configs['standard_work_days'] ?? 26);
        $workHours = (float) ($configs['standard_work_hours'] ?? 8);

        $hourlyRate = $staff->hourly_rate ?: ($basic / $workDays / $workHours);
        $otHours = $this->calculateAttendanceOt($staff, $month, $year);
        $otAmount = $otStrategy->calculate($hourlyRate, $otHours, 'normal');

        // claims  
        $approvedClaims = $this->getApprovedClaimsTotal($staff->staff_id, $month, $year);
        $totalAllowances = $approvedClaims + $otAmount + (float) ($manualInputs['total_allowances'] ?? 0);

        $manualDeduction = (float) ($manualInputs['manual_deduction'] ?? 0);
        $totalStatutoryDeductions = 0;
        $breakdown = ['ot_amount' => round($otAmount, 2)];

        // statutory calculations (EPF, SOCSO, EIS)
        if ($basic > 0) {
            // EPF
            $epfData = $this->strategies['epf']->calculate($staff, ['month' => $month, 'year' => $year, 'calculated_basic' => $basic]);
            $totalStatutoryDeductions += $epfData['epf_employee_rm'] ?? 0;
            $breakdown = array_merge($breakdown, $epfData);

            // SOCSO & EIS 
            $socsoEisData = $this->strategies['socso']->calculate($staff, [
                'total_hours' => $totalWorkedHours ?? 0,
                'calculated_basic' => $basic
            ]);

            $totalStatutoryDeductions += ($socsoEisData['socso_employee_rm'] ?? 0);
            $totalStatutoryDeductions += ($socsoEisData['eis_employee_rm'] ?? 0);
            $breakdown = array_merge($breakdown, $socsoEisData);
        }

        // net salary
        $totalDeductions = $totalStatutoryDeductions + $manualDeduction;
        $netSalary = ($basic + $totalAllowances) - $totalDeductions;

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
                'breakdown' => json_encode(['calculated_amounts' => $breakdown]),
                'allowance_remark' => $manualInputs['allowance_remark'] ?? null,
                'deduction_remark' => $manualInputs['deduction_remark'] ?? null,
            ]
        );
    }

    /**
     * hour calculation 
     */
    public function calculateTotalWorkedHours($userId, $month, $year)
    {
        return DB::table('attendances')
            ->where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->where('status', 'Present')
            ->whereNotNull('clock_out_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)) / 3600 as hours')
            ->value('hours') ?? 0;
    }

    /**
     * calculation for full time ot
     */
    public function calculateAttendanceOt(Staff $staff, $month, $year)
    {
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
        $standardDailyHours = $configs['standard_work_hours'] ?? 9;

        $attendances = Attendance::where('user_id', $staff->user_id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNotNull('clock_out_time')
            ->get();

        $totalOtHours = 0;
        foreach ($attendances as $record) {
            $in = Carbon::parse($record->clock_in_time);
            $out = Carbon::parse($record->clock_out_time);
            $hoursWorked = $out->floatDiffInHours($in);

            if ($hoursWorked > $standardDailyHours) {
                $totalOtHours += ($hoursWorked - $standardDailyHours);
            }
        }
        return $totalOtHours;
    }

    public function updateBatchTotals(int $batchId): void
    {
        $totalAmount = DB::table('payrolls')
            ->where('batch_id', $batchId)
            ->sum('net_salary');

        DB::table('payroll_batches')
            ->where('id', $batchId)
            ->update([
                'total_amount' => $totalAmount,
                'updated_at'   => now(),
            ]);
    }
}

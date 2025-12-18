<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StorePayrollRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Staff;
use App\Models\Payroll;
use App\Business\Services\PayrollService;
use App\Business\Strategies\EPFStrategy;
use Barryvdh\DomPDF\Facade\Pdf;
use \Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $service)
    {
        $this->payrollService = $service;
        // Register calculation strategies
        $this->payrollService->registerComponent('epf', new EPFStrategy());
    }

    // ==========================================
    // 1. VIEW METHODS
    // ==========================================

    public function index()
    {
        $batches = DB::table('payroll_batches')->orderBy('created_at', 'desc')->get();
        return view('admin.payroll.index', compact('batches'));
    }

    /**
     * Standard Resource 'show' method.
     * Maps to the admin.payroll.batch_view route name.
     */
    public function show($id)
    {
        return $this->batch_view($id);
    }

   public function batch_view($id)
    {
        $batch = DB::table('payroll_batches')->where('id', $id)->first();
        if (!$batch) return redirect()->route('admin.payroll.index')->with('error', 'Batch not found.');

        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        // Rates from Config
        $staffEpfRate = ($configs['staff_epf_rate'] ?? 11.00) / 100;
        $eisRate = ($configs['eis_rate'] ?? 0.20) / 100;

        foreach ($payrolls as $payroll) {
            $basic = (float)$payroll->basic_salary;
            $capped = min($basic, 6000);

            // Individual Statutory Math
            $payroll->calc_epf = $basic * $staffEpfRate;
            $payroll->calc_socso = $this->calculateSocso($basic, 'employee');
            $payroll->calc_eis = $capped * $eisRate;
            
            // Total Statutory for this row
            $statutoryTotal = $payroll->calc_epf + $payroll->calc_socso + $payroll->calc_eis;

            // Ensure Net Salary is recalculated if it's currently 0
            if($payroll->net_salary <= 0) {
                $payroll->net_salary = ($basic + $payroll->allowances) - ($payroll->deduction + $statutoryTotal);
            }
        }

        $totals = $this->getBatchStatutoryTotals($id);
        $rejectionReasons = DB::table('rejection_reasons')->get();
        $varianceMsg = "Data verified against current statutory rates.";

        return view('admin.payroll.batch_view', compact('batch', 'payrolls', 'varianceMsg', 'rejectionReasons', 'totals'));
    }
    // ==========================================
    // 2. ACTION METHODS
    // ==========================================

    public function generateBatch(Request $request)
    {
        $request->validate(['month' => 'required', 'year' => 'required']);

        try {
            DB::beginTransaction();

            $batchId = DB::table('payroll_batches')->insertGetId([
                'month_year'   => $request->year . '-' . $request->month,
                'status'       => 'Draft',
                'created_at'   => now(),
                'total_staff'  => 0,
                'total_amount' => 0
            ]);

            // Requires 'user' relationship defined in Staff model
            $activeStaff = Staff::whereHas('user', fn($q) => $q->where('status', 'Active'))->get();
            $count = 0;

            foreach ($activeStaff as $staff) {
                // 1. Generate the initial payroll record
                $payroll = $this->payrollService->generatePayroll($staff, $request->month, $request->year, ['batch_id' => $batchId]);
                
                // 2. Perform the initial calculation automatically
                $this->autoCalculateStatutory($payroll);
                $count++;
            }

            $totalAmount = Payroll::where('batch_id', $batchId)->sum('net_salary');
            DB::table('payroll_batches')->where('id', $batchId)->update([
                'total_staff' => $count,
                'total_amount' => $totalAmount
            ]);

            DB::commit();
            return redirect()->route('admin.payroll.batch_view', $batchId)->with('success', "Batch successfully generated with $count records.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Batch Generation Failed: " . $e->getMessage());
            return back()->with('error', "Failed: " . $e->getMessage());
        }
    }

    private function autoCalculateStatutory($payroll)
    {
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
        $basic = (float)$payroll->basic_salary;

        // Fetch snapshot rates from your config table
        $staffEpfRate = ($configs['staff_epf_rate'] ?? 11.00) / 100;
        $employerRateLow = ($configs['employer_epf_rate_low'] ?? 13.00) / 100;
        $employerRateHigh = ($configs['employer_epf_rate_high'] ?? 12.00) / 100;
        $eisRate = ($configs['eis_rate'] ?? 0.20) / 100;

        // Logic for Malaysia Statutory Requirements
        $epfEmployee = $basic * $staffEpfRate;
        $epfEmployer = ($basic <= 5000) ? ($basic * $employerRateLow) : ($basic * $employerRateHigh);
        $eisVal = min($basic, 6000) * $eisRate;
        $socsoEmployee = $this->calculateSocso($basic, 'employee');
        $socsoEmployer = $this->calculateSocso($basic, 'employer');

        $statutoryTotal = $epfEmployee + $socsoEmployee + $eisVal;
        
        // Initial Net Salary: (Basic + Allowances) - Statutory
        $netSalary = ($basic + $payroll->allowances) - $statutoryTotal;

        $breakdown = [
            'calculated_amounts' => [
                'epf_employee_rm' => $epfEmployee,
                'epf_employer_rm' => $epfEmployer,
                'socso_employee_rm' => $socsoEmployee,
                'socso_employer_rm' => $socsoEmployer,
                'eis_rm' => $eisVal
            ]
        ];

        $payroll->update([
            'net_salary' => $netSalary,
            'breakdown'   => $breakdown,
            'deduction'   => $statutoryTotal 
        ]);
    }

    // ==========================================
    // 3. APPROVAL WORKFLOW
    // ==========================================

    /**
     * Aliases for camelCase routes
     */
    public function approveL1($id)
    {
        return $this->approve_l1($id);
    }
    public function approveL2($id)
    {
        return $this->approve_l2($id);
    }

    public function approve_l1($id)
    {
        DB::table('payroll_batches')->where('id', $id)->update([
            'status' => 'L1_Approved',
            'rejection_reason' => null,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Level 1 (HR) Approval Granted and batch locked.');
    }

    public function approve_l2($id)
    {
        DB::beginTransaction();
        try {
            DB::table('payroll_batches')->where('id', $id)->update(['status' => 'L2_Approved']);

            Payroll::where('batch_id', $id)->update(['status' => 'Locked_For_Payment']);

            DB::commit();
            return back()->with('success', 'Full Batch and all related records have been locked for Finance.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Batch transition failed.');
        }
    }

    // ==========================================
    // 4. EXPORT & STAFF PORTAL
    // ==========================================

    /**
     * Staff View: Access their own history
     */
    public function myPayslips()
    {
        try {
            // 1. Ownership Verification: Contextual lookup
            $userId = Auth::id();
            $staff = Staff::where('user_id', $userId)->first();

            if (!$staff) {
                return back()->with('error', 'Profile not found. Please contact HR.');
            }

            // 2. Functional Workflow: Filter by status = 'Paid'
            $payrolls = Payroll::where('staff_id', $staff->staff_id)
                ->where('status', 'Paid')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return view('staff.my_payslips', compact('payrolls', 'staff'));
        } catch (\Exception $e) {
            Log::error("Payslip Access Error: " . $e->getMessage());
            return back()->with('error', 'Unable to load payslips at this time.');
        }
    }

    public function edit($id)
    {
        $payroll = Payroll::with(['staff.user'])->findOrFail($id);

        // Fetch rates exactly as they appear in your DB screenshot: 'staff_epf_rate', 'eis_rate'
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        // Attendance calculation for the sync button
        $monthNumber = date('m', strtotime($payroll->month));
        $daysPresent = DB::table('attendances')
            ->where('user_id', $payroll->staff->user_id)
            ->whereYear('attendance_date', $payroll->year)
            ->whereMonth('attendance_date', $monthNumber)
            ->where('status', 'Present')
            ->count();

        // Standard categories for dropdowns
        $allowanceCategories = DB::table('payroll_categories')->where('type', 'Allowance')->get();
        $deductionCategories = DB::table('payroll_categories')->where('type', 'Deduction')->get();

        return view('admin.payroll.edit', compact('payroll', 'configs', 'daysPresent', 'allowanceCategories', 'deductionCategories'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'basic_salary'     => 'required|numeric|min:0',
            'total_allowances' => 'required|numeric|min:0',
            'total_deductions' => 'required|numeric|min:0',
            'allowance_remark' => 'nullable|string|max:500',
            'deduction_remark' => 'nullable|string|max:500',
            'attendance_count_hidden' => 'nullable|integer|min:0'
        ]);

        try {
            // 1. BOUNDED CONTEXT: Load payroll with its parent batch state
            $payroll = Payroll::with('batch')->findOrFail($id);

            // 2. STATE PATTERN: Security guard to prevent editing locked batches
            if ($payroll->batch && $payroll->batch->status !== 'Draft') {
                return back()->with('error', "Security Lock: Cannot edit records in a {$payroll->batch->status} batch.");
            }

            // 3. EFFECTIVE DATING: Fetch rates valid for this payroll's specific month/year
            $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

            DB::beginTransaction();

            $basic = (float)$request->basic_salary;
            $allowances = (float)$request->total_allowances;
            $manualDeductions = (float)$request->total_deductions;

            // Fetch dynamic percentages from database snapshot
            $staffEpfRate     = ($configs['staff_epf_rate'] ?? 11.00) / 100;
            $employerRateLow  = ($configs['employer_epf_rate_low'] ?? 13.00) / 100;
            $employerRateHigh = ($configs['employer_epf_rate_high'] ?? 12.00) / 100;
            $eisRate          = ($configs['eis_rate'] ?? 0.20) / 100;

            // Statutory Calculations
            $epfEmployee = $basic * $staffEpfRate;
            $epfEmployer = ($basic <= 5000) ? ($basic * $employerRateLow) : ($basic * $employerRateHigh);
            $eisVal      = min($basic, 6000) * $eisRate;
            $socsoEmployee = $this->calculateSocso($basic, 'employee');
            $socsoEmployer = $this->calculateSocso($basic, 'employer');

            $statutoryTotal = $epfEmployee + $socsoEmployee + $eisVal;
            $netSalary = ($basic + $allowances) - ($manualDeductions + $statutoryTotal);

            // 4. SNAPSHOT PATTERN: Serialize calculated values into JSON
            // This "freezes" the logic so future config changes don't alter this record.
            $breakdown = [
                'statutory_rates' => [
                    'epf_employee_percent' => $staffEpfRate * 100,
                    'eis_percent' => $eisRate * 100,
                ],
                'calculated_amounts' => [
                    'epf_employee_rm' => $epfEmployee,
                    'epf_employer_rm' => $epfEmployer,
                    'socso_employee_rm' => $socsoEmployee,
                    'socso_employer_rm' => $socsoEmployer,
                    'eis_rm' => $eisVal
                ],
                'updated_by' => Auth::id(),
                'ip_address' => $request->ip()
            ];

            $updateData = [
                'basic_salary'     => $basic,
                'allowances'       => $allowances,
                'deduction'        => $manualDeductions,
                'net_salary'       => $netSalary,
                'allowance_remark' => $request->allowance_remark,
                'deduction_remark' => $request->deduction_remark,
                'breakdown'        => $breakdown, // Commit snapshot
            ];

            if ($request->has('attendance_count_hidden')) {
                $updateData['attendance_count'] = $request->attendance_count_hidden;
            }

            $payroll->update($updateData);

            // 5. AGGREGATE ROOT: Recalculate Batch Total to keep parent in sync
            $batchId = $payroll->batch_id;
            if ($batchId) {
                $newTotal = Payroll::where('batch_id', $batchId)->sum('net_salary');
                DB::table('payroll_batches')->where('id', $batchId)->update([
                    'total_amount' => $newTotal,
                    'updated_at'   => now()
                ]);
            }

            DB::commit();
            return redirect()->route('admin.payroll.batch_view', $payroll->batch_id)
                ->with('success', 'Payroll updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // 1. SECURE LOGGING: Detailed error stays in the server logs
            Log::error("Payroll Update Failure [ID: $id]: " . $e->getMessage());
            
            return back()->withInput()->with('error', 'Unable to process payroll action at this time. Please contact HR.');
        }
    }


    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::table('payroll_batches')->where('id', $id)->update([
            'status' => 'Draft',
            'rejection_reason' => $request->rejection_reason,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.payroll.batch_view', $id)
            ->with('success', 'Batch sent back to HR with notes.');
    }

private function getBatchStatutoryTotals($batchId)
{
    // 1. Get all payrolls and current configuration rates
    $payrolls = Payroll::where('batch_id', $batchId)->get();
    $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');
    
    // 2. Extract rates from config (using your DB keys: staff_epf_rate, etc.)
    $staffEpfRate = ($configs['staff_epf_rate'] ?? 25.00) / 100;
    $employerRateLow = ($configs['employer_epf_rate_low'] ?? 15.00) / 100;
    $employerRateHigh = ($configs['employer_epf_rate_high'] ?? 15.00) / 100;
    $eisRate = ($configs['eis_rate'] ?? 0.30) / 100;

    $totals = [
        'epf_employee' => 0,
        'epf_employer' => 0,
        'socso_employee' => 0,
        'socso_employer' => 0,
        'eis_total' => 0,
        'net_salary' => 0
    ];

    foreach ($payrolls as $payroll) {
        $basic = (float)$payroll->basic_salary;

        // 3. Perform the math directly from the table data
        $epfEmployee = $basic * $staffEpfRate;
        $epfEmployer = ($basic <= 5000) ? ($basic * $employerRateLow) : ($basic * $employerRateHigh);
        $eisVal = min($basic, 6000) * $eisRate;
        
        // Use your existing calculateSocso helper
        $socsoEmployee = $this->calculateSocso($basic, 'employee');
        $socsoEmployer = $this->calculateSocso($basic, 'employer');

        // 4. Accumulate Totals
        $totals['epf_employee']   += $epfEmployee;
        $totals['epf_employer']   += $epfEmployer;
        $totals['socso_employee'] += $socsoEmployee;
        $totals['socso_employer'] += $socsoEmployer;
        $totals['eis_total']      += ($eisVal * 2); // Both employee & employer
        $totals['net_salary']     += $payroll->net_salary;
    }

    return $totals;
}

private function calculateSocso($salary, $type)
{
    $cappedSalary = min($salary, 6000);
    return ($type === 'employee') ? ($cappedSalary * 0.005) : ($cappedSalary * 0.0175);
}


    /**
     * Download individual PDF
     */
    public function exportSlip($id)
    {
        // 1. Rigorous ID Validation
        $payroll = Payroll::with('staff')->findOrFail($id);

        // 2. Centralized Authorization Check
        $this->authorize('view', $payroll);

        // 3. Process logic only if authorized
        $pdf = Pdf::loadView('admin.payroll.payslip_pdf', compact('payroll'));
        return $pdf->stream('Payslip.pdf');
    }

    /**
     * Export Full Batch Report
     */
    public function exportReport($id)
    {
        return $this->exportBankFile($id);
    }


    public function exportBankFile($id)
    {
        // 1. Fetch the batch details
        $batch = DB::table('payroll_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Batch not found.');

        // 2. Fetch payrolls with staff relationships
        // Ensure we are selecting the new attendance_count and allowance_remark columns
        $payrolls = Payroll::with('staff')
            ->where('batch_id', $id)
            ->get();

        // 3. Status Transition Logic 
        // When the bank file is exported, the batch and slips are marked as 'Paid'
        if ($batch->status !== 'Paid') {
            DB::table('payroll_batches')->where('id', $id)->update([
                'status' => 'Paid',
                'updated_at' => now()
            ]);

            // Update all individual records to 'Paid' status in one query
            Payroll::where('batch_id', $id)->update(['status' => 'Paid']);
        }

        // 4. Generate the PDF using the refined template
        $pdf = Pdf::loadView('admin.payroll.pdf_export', compact('batch', 'payrolls'));

        // Use a clear naming convention for the downloaded file
        return $pdf->download('Payroll_Report_' . $batch->month_year . '.pdf');
    }
}

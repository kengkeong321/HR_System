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
        
        // Check if batch exists to avoid further errors
        if (!$batch) {
            return redirect()->route('admin.payroll.index')->with('error', 'Batch not found.');
        }

        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();

        // 1. CALL THE TOTALS HELPER HERE
        $totals = $this->getBatchStatutoryTotals($id);

        // Attach attendance count to each payroll object
        foreach ($payrolls as $payroll) {
            $monthNum = date('m', strtotime($payroll->month));
            $payroll->days_present = DB::table('attendances')
                ->where('user_id', $payroll->staff->user_id)
                ->whereYear('attendance_date', $payroll->year)
                ->whereMonth('attendance_date', $monthNum)
                ->where('status', 'Present')
                ->count();
        }

        $rejectionReasons = DB::table('rejection_reasons')->get();
        $varianceMsg = "Data loaded successfully";

        // 2. PASS $totals TO THE COMPACT FUNCTION
        return view('admin.payroll.batch_view', compact(
            'batch', 
            'payrolls', 
            'varianceMsg', 
            'rejectionReasons', 
            'totals'
        ));
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
                $this->payrollService->generatePayroll($staff, $request->month, $request->year, ['batch_id' => $batchId]);
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
                // Generic User Messaging
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

        // This pluck must match the keys in your SQL dump: 'staff_epf_rate', 'eis_rate', etc.
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        // 3. Calculate attendance for the specific month
        $monthNumber = date('m', strtotime($payroll->month));
        $daysPresent = DB::table('attendances')
            ->where('user_id', $payroll->staff->user_id)
            ->whereYear('attendance_date', $payroll->year)
            ->whereMonth('attendance_date', $monthNumber)
            ->where('status', 'Present')
            ->count();

        // 4. Get active categories for the dynamic datalist dropdowns
        $allowanceCategories = DB::table('payroll_categories')
            ->where('type', 'Allowance')
            ->orderBy('name')
            ->get();

        $deductionCategories = DB::table('payroll_categories')
            ->where('type', 'Deduction')
            ->orderBy('name')
            ->get();

        return view('admin.payroll.edit', compact(
            'payroll',
            'daysPresent',
            'allowanceCategories',
            'deductionCategories',
            'configs'
        ));
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
            // In a full implementation, you would query effective_from <= $payroll_date
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
                'breakdown'        => json_encode($breakdown), // Commit snapshot
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

            // 2. GENERIC MESSAGING: User sees a friendly, non-technical message
            return back()->withInput()->with('error', 'Unable to process payroll action at this time. Please contact HR.');
        }
    }

    private function calculateSocso($salary, $type)
    {
        $cappedSalary = min($salary, 6000);
        return ($type === 'employee') ? ($cappedSalary * 0.005) : ($cappedSalary * 0.0175);
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
        $payrolls = Payroll::where('batch_id', $batchId)->get();
        
        $totals = [
            'epf_employee' => 0,
            'epf_employer' => 0,
            'socso_employee' => 0,
            'socso_employer' => 0,
            'eis_total' => 0,
            'net_salary' => 0
        ];

        foreach ($payrolls as $payroll) {
            // Pull from the JSON Snapshot created during the 'update' or 'generate' process
            $data = json_decode($payroll->breakdown, true); 
            
            $totals['epf_employee']   += $data['calculated_amounts']['epf_employee_rm'] ?? 0;
            $totals['epf_employer']   += $data['calculated_amounts']['epf_employer_rm'] ?? 0;
            $totals['socso_employee'] += $data['calculated_amounts']['socso_employee_rm'] ?? 0;
            $totals['socso_employer'] += $data['calculated_amounts']['socso_employer_rm'] ?? 0;
            // EIS is usually 0.2% from both parties in Malaysia
            $totals['eis_total']      += ($data['calculated_amounts']['eis_rm'] ?? 0) * 2; 
            $totals['net_salary']     += $payroll->net_salary;
        }

        return $totals;
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

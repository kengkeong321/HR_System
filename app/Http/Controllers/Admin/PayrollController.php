<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use App\Models\Payroll;
use App\Models\PayrollBatch;
use App\Business\Services\PayrollService;
use App\Business\Strategies\EPFStrategy;
use App\Business\Strategies\SocsoTableStrategy;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $service)
    {
        $this->payrollService = $service;

        $this->payrollService->registerComponent('epf', new EPFStrategy());
        $this->payrollService->registerComponent('socso', new SocsoTableStrategy());

        $this->middleware('auth');
        $this->middleware('role:Admin,HR,Finance');

        $this->middleware('role:Admin,HR')->only(['generateBatch', 'create', 'store', 'edit', 'update', 'approveL1']);
        $this->middleware('role:Admin,Finance')->only(['approveL2', 'exportBankFile', 'exportReport']);
    }

    public function index()
    {
        // 1. Fetch all batches
        $batches = PayrollBatch::orderBy('created_at', 'desc')->get();

        // 2. Auto-refresh Draft batches to apply new statutory rates
        foreach ($batches as $batch) {
            if ($batch->status === 'Draft') {
                $payrolls = Payroll::where('batch_id', $batch->id)->get();
                
                foreach ($payrolls as $payroll) {
                    // This call uses your dynamic service to re-calculate based on CURRENT table rates
                    $this->payrollService->calculateAndSavePayroll(
                        $payroll->staff,
                        (int)$payroll->month,
                        (int)$payroll->year,
                        (int)$batch->id,
                        [
                            'basic_salary'      => $payroll->basic_salary,
                            'total_allowances'  => $payroll->allowances,
                            'manual_deduction'  => $payroll->manual_deduction,
                            'allowance_remark'  => $payroll->allowance_remark,
                            'deduction_remark'  => $payroll->deduction_remark,
                        ]
                    );
                }
                // Update the batch total amount after re-calculating all staff
                $this->payrollService->updateBatchTotals($batch->id);
            }
        }

        return view('admin.payroll.index', compact('batches'));
    }

    public function show($id)
    {
        return $this->batch_view($id);
    }
    

    public function batch_view($id)
    {
        $batch = PayrollBatch::findOrFail($id);
        
        if ($batch->status === 'Draft') {
            $payrolls = Payroll::where('batch_id', $id)->get();
            foreach ($payrolls as $payroll) {
                // Requirement 3: Consuming the Attendance Web Service
                // We pass the data to the service which will handle the API call
                $this->payrollService->calculateAndSavePayroll(
                    $payroll->staff,
                    (int)$payroll->month,
                    (int)$payroll->year,
                    (int)$id,
                    [
                        'basic_salary' => $payroll->basic_salary,
                        'total_allowances' => $payroll->allowances,
                        'manual_deduction' => $payroll->manual_deduction
                    ]
                );
            }
            $this->payrollService->updateBatchTotals($id);
        }

        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();
        $totals = $this->getBatchStatutoryTotals($id);
        
        return view('admin.payroll.batch_view', compact('batch', 'payrolls', 'totals'));
    }

    public function generateBatch(Request $request)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        $request->validate([
            'year' => "required|integer|min:2024|max:$currentYear",
            'month' => [
                'required',
                'integer',
                'between:1,12',
                function ($attribute, $value, $fail) use ($request, $currentYear, $currentMonth) {
                    if ($request->year == $currentYear && $value > $currentMonth) {
                        $fail('Cannot generate payroll for the future.');
                    }
                },
            ],
        ]);

        try {
            DB::beginTransaction();

            $monthYear = sprintf('%d-%02d', $request->year, $request->month);

            $batch = PayrollBatch::firstOrCreate(
                ['month_year' => $monthYear],
                ['status' => 'Draft', 'total_staff' => 0, 'total_amount' => 0]
            );

            if (!$batch->wasRecentlyCreated) {
                $batch->update(['status' => 'Draft']);
            }

            $activeStaff = Staff::whereHas('user', fn($q) => $q->where('status', 'Active'))->get();

            foreach ($activeStaff as $staff) {
                $this->payrollService->calculateAndSavePayroll(
                    $staff,
                    $request->month,
                    $request->year,
                    $batch->id
                );
            }

            $this->payrollService->updateBatchTotals($batch->id);

            DB::commit();
            return redirect()->route('admin.payroll.batch_view', $batch->id)
                ->with('success', "Batch generated successfully for {$activeStaff->count()} staff.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Batch Generation Failed: " . $e->getMessage());
            return back()->with('error', "System error during generation. Check logs.");
        }
    }

  public function edit($id)
    {
        // Fetch the record with relationships
        $payroll = Payroll::with(['staff.user'])->findOrFail($id);

        // Pull directly from your NEW column
        $manualAmount = $payroll->manual_deduction ?? 0;

        // Get system configurations
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        // Parse month for attendance
        $monthNumber = is_numeric($payroll->month)
            ? (int)$payroll->month
            : \Carbon\Carbon::parse($payroll->month . " 1 " . $payroll->year)->month;

        // Count present days
        $daysPresent = DB::table('attendances')
            ->where('user_id', $payroll->staff->user_id)
            ->whereYear('attendance_date', $payroll->year)
            ->whereMonth('attendance_date', $monthNumber)
            ->where('status', 'Present')
            ->count();

        $allowanceCategories = DB::table('payroll_categories')->where('type', 'Allowance')->get();
        $deductionCategories = DB::table('payroll_categories')->where('type', 'Deduction')->get();

        return view('admin.payroll.edit', compact(
            'payroll', 
            'configs', 
            'daysPresent', 
            'allowanceCategories', 
            'deductionCategories', 
            'manualAmount'
        ));
    }

  public function update(Request $request, $id)
{
    $request->validate([
        'basic_salary'     => 'required|numeric|min:0',
        'total_allowances' => 'required|numeric|min:0',
        'manual_deduction' => 'required|numeric|min:0', // Matches your new column
    ]);

    try {
        DB::beginTransaction();
        $payroll = Payroll::findOrFail($id);

        // We pass the data to the service. 
        // The service now fetches the NEW 12% rate from the table.
        $inputs = [
            'basic_salary'      => $request->basic_salary,
            'total_allowances'  => $request->total_allowances,
            'manual_deduction'  => $request->manual_deduction, 
            'allowance_remark'  => $request->allowance_remark,
            'deduction_remark'  => $request->deduction_remark,
        ];

        // This method recalculates the 'deduction' and 'net_salary' columns.
        $this->payrollService->calculateAndSavePayroll(
            $payroll->staff,
            $payroll->month,
            $payroll->year,
            $payroll->batch_id,
            $inputs
        );

        // Update the top Audit Summary totals
        $this->payrollService->updateBatchTotals($payroll->batch_id);

        DB::commit();
        return redirect()->route('admin.payroll.batch_view', $payroll->batch_id)
            ->with('success', 'Calculation updated with new statutory rates.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Update failed: ' . $e->getMessage());
    }
}

    public function approveL1($id)
    {
        $batch = PayrollBatch::findOrFail($id);

        if ($batch->status !== 'Draft') {
            return back()->with('error', 'Batch must be in Draft status for Level 1 approval.');
        }

        $batch->update([
            'status' => 'L1_Approved',
            'rejection_reason' => null,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Level 1 (HR) Approval granted successfully.');
    }

    public function approveL2($id)
    {
        $batch = PayrollBatch::findOrFail($id);

        if ($batch->status !== 'L1_Approved') {
            return back()->with('error', 'Batch must be Level 1 Approved before Level 2 approval.');
        }

        DB::transaction(function () use ($batch, $id) {
            $batch->update(['status' => 'L2_Approved']);
            Payroll::where('batch_id', $id)->update(['status' => 'Locked_For_Payment']);
        });

        return back()->with('success', 'Level 2 (Finance) Approval granted. Batch locked for payment.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $batch = PayrollBatch::findOrFail($id);

        $batch->update([
            'status' => 'Draft',
            'rejection_reason' => $request->rejection_reason,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.payroll.batch_view', $id)
            ->with('warning', 'Batch rejected and returned to Draft status.');
    }

    public function exportBankFile($id)
    {
        $batch = PayrollBatch::findOrFail($id);
        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();

        // Auto-mark as paid when exporting
        if ($batch->status !== 'Paid') {
            DB::transaction(function () use ($batch, $id) {
                $batch->update(['status' => 'Paid']);
                Payroll::where('batch_id', $id)->update(['status' => 'Paid']);
            });
        }

        $pdf = Pdf::loadView('admin.payroll.pdf_export', compact('batch', 'payrolls'));
        return $pdf->download('Payroll_Report_' . $batch->month_year . '.pdf');
    }

    public function exportSlip($id)
    {
        $payroll = Payroll::with('staff')->findOrFail($id);

        // Authorization check
        if (
            Auth::user()->role !== 'Admin' &&
            Auth::user()->role !== 'HR' &&
            Auth::id() !== $payroll->staff->user_id
        ) {
            abort(403, 'Unauthorized access to payslip.');
        }

        $pdf = Pdf::loadView('admin.payroll.payslip_pdf', compact('payroll'));
        return $pdf->stream('Payslip_' . $payroll->staff->staff_id . '_' . $payroll->month . '_' . $payroll->year . '.pdf');
    }

    public function myPayslips()
    {
        try {
            $staff = Staff::where('user_id', Auth::id())->firstOrFail();

            $payrolls = Payroll::where('staff_id', $staff->staff_id)
                ->where('status', 'Paid')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return view('staff.my_payslips', compact('payrolls', 'staff'));
        } catch (\Exception $e) {
            Log::error("Failed to load payslips for user " . Auth::id() . ": " . $e->getMessage());
            return back()->with('error', 'Unable to load payslips. Please contact HR.');
        }
    }

    /**
     * Calculate batch statutory totals from breakdown JSON
     */
    private function getBatchStatutoryTotals($batchId)
    {
        // Use selectRaw for better performance when summing columns
        $summary = Payroll::where('batch_id', $batchId)
            ->selectRaw('
                SUM(basic_salary) as total_basic, 
                SUM(allowances) as total_allowances, 
                SUM(manual_deduction) as total_manual,
                SUM(net_salary) as total_net
            ')
            ->first();

        $payrolls = Payroll::where('batch_id', $batchId)->get();
        $epf = 0; $socso = 0; $eis = 0;

        foreach ($payrolls as $payroll) {
            $breakdown = is_string($payroll->breakdown) ? json_decode($payroll->breakdown, true) : $payroll->breakdown;
            $amounts = $breakdown['calculated_amounts'] ?? [];
            
            // Sum Employee portion only for Audit Verification
            $epf   += ($amounts['epf_employee_rm'] ?? 0);
            $socso += ($amounts['socso_employee_rm'] ?? 0);
            $eis   += ($amounts['eis_employee_rm'] ?? 0);
        }

        return [
            'basic_salary'     => $summary->total_basic ?? 0,
            'allowances'       => $summary->total_allowances ?? 0,
            'manual_deduction' => $summary->total_manual ?? 0, // Passed to Blade
            'epf_total'        => $epf,
            'socso_total'      => $socso,
            'eis_total'        => $eis,
        ];
    }
    
}

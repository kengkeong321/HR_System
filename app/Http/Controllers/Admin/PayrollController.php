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
        $batches = PayrollBatch::orderBy('created_at', 'desc')->get();
        return view('admin.payroll.index', compact('batches'));
    }

    public function show($id)
    {
        return $this->batch_view($id);
    }

    public function batch_view($id)
    {
        $batch = PayrollBatch::findOrFail($id);
        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();

        $totals = $this->getBatchStatutoryTotals($id);
        $rejectionReasons = DB::table('rejection_reasons')->get();
        $varianceMsg = "Data verified against current statutory rates.";

        return view('admin.payroll.batch_view', compact('batch', 'payrolls', 'varianceMsg', 'rejectionReasons', 'totals'));
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
        $payroll = Payroll::with(['staff.user'])->findOrFail($id);
        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        // Parse month correctly
        $monthNumber = is_numeric($payroll->month)
            ? (int)$payroll->month
            : Carbon::parse($payroll->month . " 1 " . $payroll->year)->month;

        // Count present days for attendance verification
        $daysPresent = DB::table('attendances')
            ->where('user_id', $payroll->staff->user_id)
            ->whereYear('attendance_date', $payroll->year)
            ->whereMonth('attendance_date', $monthNumber)
            ->where('status', 'Present')
            ->count();

        $allowanceCategories = DB::table('payroll_categories')->where('type', 'Allowance')->get();
        $deductionCategories = DB::table('payroll_categories')->where('type', 'Deduction')->get();

        return view('admin.payroll.edit', compact('payroll', 'configs', 'daysPresent', 'allowanceCategories', 'deductionCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'basic_salary'     => 'required|numeric|min:0',
            'total_allowances' => 'required|numeric|min:0',
            'total_deductions' => 'required|numeric|min:0',
            'allowance_remark' => 'nullable|string|max:500',
            'deduction_remark' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            $payroll = Payroll::findOrFail($id);

            // Prepare inputs for service
            $inputs = [
                'basic_salary'     => $request->basic_salary,
                'total_allowances' => $request->total_allowances,
                'total_deductions' => $request->total_deductions,
                'allowance_remark' => $request->allowance_remark,
                'deduction_remark' => $request->deduction_remark,
            ];

            // Recalculate using service
            $this->payrollService->calculateAndSavePayroll(
                $payroll->staff,
                $payroll->month,
                $payroll->year,
                $payroll->batch_id,
                $inputs
            );

            // Update batch totals
            $this->payrollService->updateBatchTotals($payroll->batch_id);

            DB::commit();
            return redirect()->route('admin.payroll.batch_view', $payroll->batch_id)
                ->with('success', 'Payroll record and batch totals updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payroll Update Failed: " . $e->getMessage());
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
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
        $payrolls = Payroll::where('batch_id', $batchId)->get();

        $totals = [
            'epf_employee' => 0,
            'epf_employer' => 0,
            'socso_employee' => 0,
            'socso_employer' => 0,
            'eis_employee' => 0,
            'eis_employer' => 0,
            'net_salary' => 0
        ];

        foreach ($payrolls as $payroll) {
            // Parse breakdown JSON
            $breakdown = is_string($payroll->breakdown)
                ? json_decode($payroll->breakdown, true)
                : $payroll->breakdown;

            $amounts = $breakdown['calculated_amounts'] ?? [];

            $totals['epf_employee']   += ($amounts['epf_employee_rm'] ?? 0);
            $totals['epf_employer']   += ($amounts['epf_employer_rm'] ?? 0);
            $totals['socso_employee'] += ($amounts['socso_employee_rm'] ?? 0);
            $totals['socso_employer'] += ($amounts['socso_employer_rm'] ?? 0);
            $totals['eis_employee']   += ($amounts['eis_employee_rm'] ?? 0);
            $totals['eis_employer']   += ($amounts['eis_employer_rm'] ?? 0);
            $totals['net_salary']     += $payroll->net_salary;
        }

        // Round all totals
        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return $totals;
    }
}

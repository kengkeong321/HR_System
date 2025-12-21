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

        $this->middleware('role:Admin,HR,Finance')->except(['exportSlip']);

        $this->middleware('role:Admin,HR')->only(['generateBatch', 'create', 'store', 'edit', 'update', 'approveL1']);
        $this->middleware('role:Admin,Finance,HR')->only(['exportReport']);
        $this->middleware('role:Admin,Finance')->only(['approveL2', 'exportBankFile']);
    }

    public function index()
    {
        $currentYear = now()->year;

        $batches = PayrollBatch::orderBy('created_at', 'desc')->get();

        foreach ($batches as $batch) {
            if ($batch->status === 'Draft') {
                $payrolls = Payroll::where('batch_id', $batch->id)->get();

                foreach ($payrolls as $payroll) {
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
                $this->payrollService->updateBatchTotals($batch->id);
            }
        }

        foreach ($batches as $batch) {
            if ($batch->status === 'Draft') {
                $payrolls = Payroll::where('batch_id', $batch->id)->get();

                foreach ($payrolls as $payroll) {
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
                $this->payrollService->updateBatchTotals($batch->id);
            }
        }
        return view('admin.payroll.index', compact('batches', 'currentYear'));
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
        $payroll = Payroll::with(['staff.user'])->findOrFail($id);
        $manualAmount = $payroll->manual_deduction ?? 0;

        $configs = DB::table('payroll_configs')->pluck('config_value', 'config_key');

        $monthNumber = is_numeric($payroll->month)
            ? (int)$payroll->month
            : \Carbon\Carbon::parse($payroll->month . " 1 " . $payroll->year)->month;

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
            'manual_deduction' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            $payroll = Payroll::findOrFail($id);

            $inputs = [
                'basic_salary'      => $request->basic_salary,
                'total_allowances'  => $request->total_allowances,
                'manual_deduction'  => $request->manual_deduction,
                'allowance_remark'  => $request->allowance_remark,
                'deduction_remark'  => $request->deduction_remark,
            ];

            $this->payrollService->calculateAndSavePayroll(
                $payroll->staff,
                $payroll->month,
                $payroll->year,
                $payroll->batch_id,
                $inputs
            );

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
            'remark' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Level 1 (HR) Approval granted successfully.');
    }

    public function approveL2($id)
    {
        try {
            DB::beginTransaction();

            $batch = PayrollBatch::findOrFail($id);

            if (!in_array(Auth::user()->role, ['Finance', 'Admin'])) {
                return back()->with('error', 'Unauthorized: Only Finance can authorize final payment.');
            }

            if ($batch->status !== 'L1_Approved') {
                DB::rollBack();
                return back()->with('error', 'Batch must be L1 Approved before final authorization.');
            }

            $batch->update([
                'status' => 'Paid',
                'approved_by' => Auth::id(),
                'updated_at' => now()
            ]);

            Payroll::where('batch_id', $id)->update([
                'status' => 'Paid',
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Batch Authorized. Staff can now view their payslips.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("L2 Authorization Error: " . $e->getMessage(), [
                'batch_id' => $id,
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'Final authorization failed.');
        }
    }


    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000'
        ]);

        if (!in_array(Auth::user()->role, ['Finance', 'Admin'])) {
            return back()->with('error', 'Unauthorized: Only Finance can reject audit batches.');
        }

        try {
            DB::beginTransaction();

            $batch = PayrollBatch::findOrFail($id);

            if ($batch->status !== 'L1_Approved') {
                DB::rollBack();
                return back()->with('error', 'Current status (' . $batch->status . ') cannot be rejected.');
            }

            $batch->update([
                'status' => 'Draft',
                'remark' => $validated['rejection_reason'],
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->route('admin.payroll.index')
                ->with('warning', 'Payroll batch rejected and returned to HR for corrections.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll Rejection Error: ' . $e->getMessage());
            return back()->with('error', 'System error during rejection: ' . $e->getMessage());
        }
    }

    public function exportReport($id)
    {
        try {
            $batch = PayrollBatch::findOrFail($id);

            if (!in_array(Auth::user()->role, ['HR', 'Finance', 'Admin'])) {
                return abort(403, 'Unauthorized access to bank reports.');
            }

            $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();

            if ($payrolls->isEmpty()) {
                return back()->with('error', 'No payroll records found for this batch.');
            }

            if ($batch->status !== 'Paid') {
                DB::transaction(function () use ($batch, $id) {
                    $batch->update(['status' => 'Paid']);
                    Payroll::where('batch_id', $id)->update(['status' => 'Paid']);
                });
            }

            $pdf = Pdf::loadView('admin.payroll.pdf_export', compact('batch', 'payrolls'));
            return $pdf->download('Payroll_Report_' . $batch->month_year . '.pdf');
        } catch (\Exception $e) {
            Log::error("Payroll Export Error: " . $e->getMessage());
            return back()->with('error', 'Failed to generate the report.');
        }
    }

    public function exportSlip($id)
    {
        $payroll = Payroll::with('staff')->findOrFail($id);

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
     * Calculate batch statutory totals 
     */
    private function getBatchStatutoryTotals($batchId)
    {
        $summary = Payroll::where('batch_id', $batchId)
            ->selectRaw('
                SUM(basic_salary) as total_basic, 
                SUM(allowances) as total_allowances, 
                SUM(manual_deduction) as total_manual,
                SUM(net_salary) as total_net
            ')
            ->first();

        $payrolls = Payroll::where('batch_id', $batchId)->get();
        $epf = 0;
        $socso = 0;
        $eis = 0;

        foreach ($payrolls as $payroll) {
            $breakdown = is_string($payroll->breakdown) ? json_decode($payroll->breakdown, true) : $payroll->breakdown;
            $amounts = $breakdown['calculated_amounts'] ?? [];

            $epf   += ($amounts['epf_employee_rm'] ?? 0);
            $socso += ($amounts['socso_employee_rm'] ?? 0);
            $eis   += ($amounts['eis_employee_rm'] ?? 0);
        }

        return [
            'basic_salary'     => $summary->total_basic ?? 0,
            'allowances'       => $summary->total_allowances ?? 0,
            'manual_deduction' => $summary->total_manual ?? 0,
            'epf_total'        => $epf,
            'socso_total'      => $socso,
            'eis_total'        => $eis,
        ];
    }
}

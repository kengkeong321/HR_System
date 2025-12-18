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
        if (!$batch) {
            return redirect()->route('admin.payroll.index')->with('error', 'Batch not found.');
        }

        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();
        $varianceMsg = "Data loaded successfully"; 
        
        return view('admin.payroll.batch_view', compact('batch', 'payrolls', 'varianceMsg'));
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
    public function approveL1($id) { return $this->approve_l1($id); }
    public function approveL2($id) { return $this->approve_l2($id); }

    public function approve_l1($id)
    {
        DB::table('payroll_batches')->where('id', $id)->update([
            'status' => 'L1_Approved', 
            'updated_at' => now()
        ]);
        return back()->with('success', 'Level 1 (HR) Approval Granted.');
    }

    public function approve_l2($id)
    {
        DB::table('payroll_batches')->where('id', $id)->update([
            'status' => 'L2_Approved', 
            'updated_at' => now()
        ]);
        return back()->with('success', 'Level 2 (Finance) Approval Granted.');
    }

    // ==========================================
    // 4. EXPORT & STAFF PORTAL
    // ==========================================

    /**
     * Staff View: Access their own history
     */
    public function myPayslips()
    {
        $user = auth()->user();
        // Assuming email matches between User and Staff tables
        $staff = Staff::where('email', $user->email)->first();
        
        if (!$staff) {
            return back()->with('error', 'No staff profile linked to this account.');
        }

        $payrolls = Payroll::where('staff_id', $staff->id)
            ->where('status', 'Paid')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.my_payslips', compact('payrolls'));
    }

    /**
     * Show the form for editing a specific staff's payroll record.
     */
    public function edit($id)
    {
        // Find the specific payroll record
        $payroll = Payroll::with('staff')->findOrFail($id);
        
        // Fetch categories for the dropdowns (Allowances/Deductions)
        $allowanceTypes = DB::table('payroll_categories')->where('type', 'Allowance')->get();
        $deductionTypes = DB::table('payroll_categories')->where('type', 'Deduction')->get();

        return view('admin.payroll.edit', compact('payroll', 'allowanceTypes', 'deductionTypes'));
    }

    /**
     * Update the specific payroll record in storage.
     */
    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        $request->validate([
            'basic_salary' => 'required|numeric',
            'net_salary' => 'required|numeric',
        ]);

        // Update the record with new values
        $payroll->update($request->all());

        return redirect()->route('admin.payroll.batch_view', $payroll->batch_id)
            ->with('success', 'Payroll record updated successfully.');
    }

    /**
     * Download individual PDF
     */
    public function exportSlip($id)
    {
        $payroll = Payroll::with('staff')->findOrFail($id);
        
        // Basic Security Check: Staff can only see their own
        if (auth()->user()->role !== 'Admin') {
            $staff = Staff::where('email', auth()->user()->email)->first();
            if ($payroll->staff_id !== $staff->id) abort(403);
        }

        $pdf = Pdf::loadView('admin.payroll.payslip_pdf', compact('payroll'));
        return $pdf->stream('Payslip_' . $payroll->staff->full_name . '.pdf');
    }

    /**
     * Export Full Batch Report
     */
    public function exportReport($id) { return $this->exportBankFile($id); }
    public function exportBankFile($id)
    {
        $batch = DB::table('payroll_batches')->where('id', $id)->first();
        if (!$batch) return back()->with('error', 'Batch not found.');

        $payrolls = Payroll::with('staff')->where('batch_id', $id)->get();
        
        // Finalize status to Paid upon report export
        if ($batch->status !== 'Paid') {
            DB::table('payroll_batches')->where('id', $id)->update(['status' => 'Paid', 'updated_at' => now()]);
            Payroll::where('batch_id', $id)->update(['status' => 'Paid']);
        }

        $pdf = Pdf::loadView('admin.payroll.pdf_export', compact('batch', 'payrolls'));
        return $pdf->download('Payroll_Report_' . $batch->month_year . '.pdf');
    }
}
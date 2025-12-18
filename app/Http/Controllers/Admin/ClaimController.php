<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Payroll;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::where('staff_id', Auth::user()->staff->staff_id)
            ->latest()
            ->get();

        return view('staff.claims.index', compact('claims'));
    }

    // Show the form
    public function create()
    {
        return view('staff.claims.create');
    }

    // Handle the submission
   public function store(Request $request)
    {
        // 1. Validate (matching the 'name' attribute in your HTML)
        $request->validate([
            'claim_type'  => 'required',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'receipt'     => 'nullable|mimes:pdf,jpg,png|max:2048'
        ]);

        try {
            // 2. Prepare data
            $claim = new Claim();
            $claim->staff_id    = Auth::user()->staff->staff_id;
            $claim->claim_type  = $request->claim_type;
            $claim->amount      = $request->amount;
            $claim->description = $request->description;
            $claim->status      = 'Pending';

            // 3. Handle File Upload
            if ($request->hasFile('receipt')) {
                // Stores in storage/app/public/receipts
                $path = $request->file('receipt')->store('receipts', 'public');
                $claim->receipt_path = $path;
            }

            $claim->save();

            return redirect()->route('staff.claims.index')->with('success', 'Claim submitted successfully!');

        } catch (\Exception $e) {
            Log::error("Claim Submission Error: " . $e->getMessage());
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

   public function approve(Claim $claim)
    {
        DB::transaction(function () use ($claim) {
            // 1. Mark Claim as Approved (using the Facade to avoid IDE errors)
            $claim->update([
                'status' => 'Approved', 
                // 'action_by' is likely a column you need for auditing
                'action_by' => Auth::id() 
            ]);

            // 2. Find the current month's "Draft" payroll for this staff
            $payroll = Payroll::where('staff_id', $claim->staff_id)
                ->whereHas('batch', fn($q) => $q->where('status', 'Draft'))
                ->first();

            if ($payroll) {
                // 3. Update Allowances
                $payroll->allowances += $claim->amount;
                
                // 4. Build the Remark (Removed 'ocr_merchant' since it's missing from your DB)
                $newRemark = " | " . $claim->claim_type . ": " . $claim->description;
                $payroll->allowance_remark = ($payroll->allowance_remark ?? '') . $newRemark;
                
                $payroll->save();

                // 5. Recalculate Net Salary
                $this->recalculatePayrollNet($payroll);
            }
        });

        return back()->with('success', 'Claim approved and payroll adjusted.');
    }
    private function recalculatePayrollNet($payroll)
    {
        // Basic Example: Adjust this based on your business logic
        $earnings = $payroll->basic_salary + $payroll->allowances + $payroll->overtime;
        $deductions = $payroll->epf + $payroll->socso + $payroll->pcb;
        
        $payroll->net_salary = $earnings - $deductions;
        $payroll->save();
    }
}

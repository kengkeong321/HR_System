<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Payroll;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::where('staff_id', auth()->user()->staff->staff_id)
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
        $request->validate([
            'claim_type' => 'required',
            'amount'     => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'receipt'    => 'nullable|mimes:pdf,jpg,png|max:2048'
        ]);

        try {
            // Place your logic here (Step 3 in the previous reply)
            return redirect()->route('staff.dashboard')->with('success', 'Claim submitted.');
        } catch (\Exception $e) {
            Log::error("Claim Error: " . $e->getMessage());
            return back()->with('error', 'Submission failed.');
        }
    }

    public function approve(Claim $claim)
    {
        DB::transaction(function () use ($claim) {
            // 1. Mark Claim as Approved
            $claim->update(['status' => 'Approved', 'action_by' => auth()->id()]);

            // 2. Find the current month's "Draft" payroll for this staff
            $payroll = Payroll::where('staff_id', $claim->staff_id)
                ->whereHas('batch', fn($q) => $q->where('status', 'Draft'))
                ->first();

            if ($payroll) {
                // 3. Increment Allowances and append Remark (Audit Trail)
                $payroll->allowances += $claim->amount;
                $payroll->allowance_remark .= " | " . $claim->claim_type . ": " . $claim->ocr_merchant;
                $payroll->save();

                // 4. Trigger Recalculate Net Salary for the row
                $this->recalculatePayrollNet($payroll);
            }
        });

        return back()->with('success', 'Claim approved and payroll adjusted.');
    }
}

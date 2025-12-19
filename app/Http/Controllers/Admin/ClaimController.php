<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Payroll;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'Pending');

        $claims = Claim::with('staff.user')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalClaimed = Claim::where('status', 'Approved')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $highValueClaims = Claim::where('amount', '>', 1000)
            ->where('status', 'Pending')
            ->count();

        return view('admin.claims.index', compact('claims', 'status', 'totalClaimed', 'highValueClaims'));
    }

    public function create()
    {
        return view('staff.claims.create');
    }

    // 2. Process the form submission
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048', 
        ]);

       $userId = Auth::id();
            $staff = Staff::where('user_id', $userId)->first();

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        \App\Models\Claim::create([
            'staff_id' => $staff->staff_id,
            'claim_type' => $request->claim_type,
            'description' => $request->description,
            'amount' => $request->amount,
            'receipt_path' => $path,
            'status' => 'Pending', 
        ]);

        return redirect()->route('staff.claims.index')->with('success', 'Claim submitted successfully!');
    }

    public function myClaims()
    {
        $userId = Auth::id();
        $staff = Staff::where('user_id', $userId)->first();

        $claims = \App\Models\Claim::where('staff_id', $staff->staff_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('staff.claims.index', compact('claims'));
    }

    public function approve($id)
    {
        if (Auth::user()->role !== 'HR') {
            return back()->with('error', 'Unauthorized: Only HR can approve claims.');
        }

        try {
            DB::beginTransaction();

            $claim = Claim::findOrFail($id);

            if ($claim->status !== 'Pending') {
                return back()->with('error', 'Claim is not pending.');
            }

            $claim->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->id,
                'approved_at' => now()
            ]);

            // Sync with Payroll
            $currentPayroll = Payroll::where('staff_id', $claim->staff_id)
                ->whereHas('batch', fn($q) => $q->where('status', 'Draft'))
                ->first();

            if ($currentPayroll) {
                $newAllowance = $currentPayroll->allowances + $claim->amount;
                $newRemark = $currentPayroll->allowance_remark . " | Claim #{$claim->id}: {$claim->description}";

                $currentPayroll->update([
                    'allowances' => $newAllowance,
                    'allowance_remark' => ltrim($newRemark, ' | '),
                    'net_salary' => ($currentPayroll->basic_salary + $newAllowance) - ($currentPayroll->deduction + $this->getStatutoryTotal($currentPayroll))
                ]);
            }

            DB::commit();
            return back()->with('success', 'Claim Approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approval Error: " . $e->getMessage());
            return back()->with('error', 'Error processing claim.');
        }
    }

    public function reject(Request $request, $id)
    {
        // 1. MANUAL CHECK
        if (Auth::user()->role !== 'HR') {
            return back()->with('error', 'Unauthorized: Only HR can reject claims.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        $claim = Claim::findOrFail($id);

        $claim->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => Auth::user()->id
        ]);

        return back()->with('success', 'Claim rejected.');
    }

    private function getStatutoryTotal($payroll)
    {
        $data = json_decode($payroll->breakdown, true);
        if (!$data) return 0;

        return ($data['calculated_amounts']['epf_employee_rm'] ?? 0)
            + ($data['calculated_amounts']['socso_employee_rm'] ?? 0)
            + ($data['calculated_amounts']['eis_rm'] ?? 0);
    }
}

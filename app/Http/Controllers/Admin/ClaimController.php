<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Claim;
use App\Models\Payroll;
use App\Models\Staff;
use App\Business\Services\PayrollService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HR,Admin')->only(['approve', 'reject']);
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'Pending');

        if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $status = 'Pending';
        }

        $claims = Claim::with(['staff.user', 'staff.department'])
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
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

    public function store(Request $request)
    {

        $request->validate([
            'claim_type' => 'required|string|in:Medical,Travel,Entertainment,Extra Class,Other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:10000',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        $staff = Staff::where('user_id', Auth::id())->firstOrFail();

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        Claim::create([
            'staff_id' => $staff->staff_id,
            'claim_type' => $request->claim_type,
            'description' => $request->description,
            'amount' => $request->amount,
            'receipt_path' => $path,
            'status' => 'Pending',
        ]);

        return redirect()->route('staff.claims.index')->with('success', 'Claim submitted successfully!');
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            if (!in_array(Auth::user()->role, ['HR', 'Admin'])) {
                return back()->with('error', 'Unauthorized: Request denied.');
            }

            $claim = Claim::findOrFail($id);

            $request->validate([
                'approved_amount' => 'required|numeric|min:0.01|max:' . $claim->amount,
                'remark' => 'nullable|string|max:255'
            ]);

            if ($claim->status !== 'Pending') {
                return back()->with('error', 'Action denied: Claim is already ' . $claim->status);
            }

            $approvedAmount = (float)$request->input('approved_amount');

            $claim->update([
                'status' => 'Approved',
                'amount' => $approvedAmount,
                'approved_by' => Auth::user()->user_id,
                'approved_at' => now(),
                'is_seen' => 0,
                'rejection_reason' => null,
                'admin_remark' => $request->remark
            ]);

            $currentPayroll = Payroll::where('staff_id', $claim->staff_id)
                ->whereHas('batch', fn($q) => $q->where('status', 'Draft'))
                ->first();

            if ($currentPayroll) {
                $newAllowance = $currentPayroll->allowances + $approvedAmount;
                $newRemark = trim($currentPayroll->allowance_remark . " | Claim #{$claim->id}: {$claim->description}", " | ");

                $currentPayroll->update([
                    'allowances' => round($newAllowance, 2),
                    'allowance_remark' => Str::limit($newRemark, 500),
                ]);

                $payrollService = app(PayrollService::class);
                $payrollService->updateBatchTotals($currentPayroll->batch_id);

                $this->refreshNetSalary($currentPayroll);
            }

            DB::commit();
            return back()->with('success', 'Claim Approved and Payroll updated.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            return back()->with('error', 'Unauthorized: You lack sufficient permissions.');
        } catch (\Exception $e) {
            Log::error("Claim Approval Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An internal error occurred. Please contact the administrator.'
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);
            $this->authorize('verify', $claim);

            $request->validate([
                'rejection_reason' => 'required|string|min:5|max:500'
            ]);

            $claim->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => Auth::user()->user_id,
                'updated_at' => now()
            ]);

            return redirect()->route('admin.claims.index')->with('warning', 'Claim rejected.');
        } catch (\Exception $e) {
            Log::error("Claim Rejection Failure: " . $e->getMessage());
            return back()->with('error', 'Error: Request could not be completed.');
        }
    }

    private function refreshNetSalary($payroll)
    {
        $statutoryTotal = $this->getStatutoryTotal($payroll);
        $newNet = ($payroll->basic_salary + $payroll->allowances) - ($statutoryTotal + ($payroll->manual_deductions ?? 0));
        $payroll->update(['net_salary' => round($newNet, 2)]);
    }

    private function getStatutoryTotal($payroll)
    {
        $data = is_array($payroll->breakdown) ? $payroll->breakdown : json_decode($payroll->breakdown, true);
        if (!isset($data['calculated_amounts'])) return 0;

        $amounts = $data['calculated_amounts'];
        return ($amounts['epf_employee_rm'] ?? 0)
            + ($amounts['socso_employee_rm'] ?? 0)
            + ($amounts['eis_employee_rm'] ?? 0);
    }
}

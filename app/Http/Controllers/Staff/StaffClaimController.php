<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Claim;
use App\Models\ClaimCategory;
use App\Models\Staff;
use Illuminate\Support\Facades\Log;

class StaffClaimController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user || !$user->staff) {
            return redirect()->back()->with('error', 'Staff profile not found.');
        }

        $staffId = $user->staff->staff_id;

        \App\Models\Claim::where('staff_id', $staffId)
            ->where('status', 'Rejected')
            ->where('is_seen', 0)
            ->update(['is_seen' => 1]);

        $claims = Claim::with('staff')
            ->where('staff_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.claims.index', compact('claims'));
    }

    public function create()
    {
        $categories = ClaimCategory::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

        return view('staff.claims.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'claim_type'   => 'required|string',
            'amount'       => 'required|numeric|min:0.01|max:999999.99',
            'description'  => 'required|string|max:1000',
            'receipt'      => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ], [
            'receipt.max' => 'The receipt is too large. Please upload a file smaller than 2MB.',
        ]);

        try {
            $staff = Staff::where('user_id', Auth::id())->first();
            if (!$staff) {
                return back()->with('error', 'Unauthorized: No staff profile linked.');
            }

            $claim = new Claim();
            $claim->staff_id    = $staff->staff_id;
            $claim->claim_type  = $request->claim_type;
            $claim->amount      = $request->amount;
            $claim->description = $request->description;
            $claim->status      = 'Pending';

            if ($request->hasFile('receipt')) {
                $path = $request->file('receipt')->store('claims/receipts', 'public');
                $claim->receipt_path = $path;
            }

            $claim->save();
            return redirect()->route('staff.claims.index')->with('success', 'Claim submitted!');
        } catch (\Exception $e) {
            Log::error('Upload Failure: ' . $e->getMessage());
            return back()->with('error', 'A system error occurred. Please try a smaller file.');
        }
    }


    public function viewReceipt($id)
{
    // [12] Validate data range: Ensure ID is numeric
    if (!is_numeric($id)) {
        abort(404);
    }

    try {
        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        // [78] Centralized Authorization Check
        // Allow if the user is a manager OR if the staff member owns the claim
        $isManager = in_array($user->role, ['Admin', 'HR', 'Finance']);
        $isOwner = ($user->staff && $user->staff->staff_id === $claim->staff_id);

        if (!$isManager && !$isOwner) {
            // [107] Prevent Information Leakage: Generic Forbidden response
            abort(403, 'Unauthorized access to this resource.');
        }

        $path = storage_path('app/public/' . $claim->receipt_path);

        if (!file_exists($path)) {
            return back()->with('error', 'The requested file does not exist.');
        }

        return response()->file($path);

    } catch (\Exception $e) {
        // [107] Error Handling: Log internally, show generic error to user
        Log::error('Receipt Access Error: ' . $e->getMessage());
        return back()->with('error', 'A system error occurred while retrieving the file.');
    }
}

    public function myHistory()
    {
        $staff = Auth::user()->staff;

        if (!$staff) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Error: Your user account is not linked to a Staff profile. Please contact HR.');
        }

        $claims = \App\Models\Claim::where('staff_id', $staff->staff_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.claims.index', compact('claims'));
    }
}

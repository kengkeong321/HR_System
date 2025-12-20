<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Claim;
use App\Models\Staff;


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

        $claims = \App\Models\Claim::with('staff')
            ->where('staff_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.claims.index', compact('claims'));
    }
    public function create()
    {
        $categories = \App\Models\ClaimCategory::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

        return view('staff.claims.create', compact('categories'));
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
   public function store(Request $request)
    {
        // 1. Validate based on your claim_categories table
        $request->validate([
            'claim_type' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        // 2. Get the staff_id linked to the logged-in user
        $staff = Staff::where('user_id', Auth::id())->firstOrFail();

        // 3. Handle file upload
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // 4. Create record using your specific column names (staff_id, receipt_path)
        Claim::create([
            'staff_id' => $staff->staff_id,
            'claim_type' => $request->claim_type,
            'description' => $request->description,
            'amount' => $request->amount,
            'receipt_path' => $path,
            'status' => 'Pending',
            'is_seen' => 0,
        ]);

        return redirect()->route('staff.claims.index')->with('success', 'Claim submitted successfully!');
    }
}

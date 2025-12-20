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

        Claim::where('staff_id', $staffId)
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
        $request->validate([
            'claim_type' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
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
            'is_seen' => 0,
        ]);

        return redirect()->route('staff.claims.index')->with('success', 'Claim submitted successfully!');
    }

    public function viewReceipt($id)
    {
        $claim = \App\Models\Claim::findOrFail($id);
        $user = Auth::user();

        $isOwner = $user->staff && $user->staff->staff_id === $claim->staff_id;

        $isManagement = in_array($user->role, ['Admin', 'HR', 'Finance']);

        if (!$isOwner && !$isManagement) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        $path = storage_path('app/public/' . $claim->receipt_path);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->file($path);
    }
}

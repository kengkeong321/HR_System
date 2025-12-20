<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Claim;

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
}

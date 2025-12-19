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
        $staff = Auth::user()->staff;

        $claims = Claim::where('staff_id', $staff->staff_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.claims.index', compact('claims'));
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

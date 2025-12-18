<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function myHistory()
    {
        // 1. Get the currently logged-in user
        $user = Auth::user();

        // 2. Check if this user has a linked Staff profile
        if (!$user->staff) {
            return redirect()->route('dashboard')
                ->with('error', 'No staff profile found for this account.');
        }

        // 3. Get payrolls sorted by most recent
        // (Assumes you added the relationships I gave you earlier)
        $payrolls = $user->staff->payrolls()->orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        // 4. Return the view
        return view('staff.payroll.my_payslips', compact('payrolls'));
    }
}
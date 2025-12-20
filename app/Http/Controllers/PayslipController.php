<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Staff;

class PayslipController extends Controller
{
    public function myHistory()
    {
        $user = Auth::user();

        if (!$user->staff) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'No staff profile found for this account.');
        }

        $payrolls = Payroll::where('staff_id', $user->staff->staff_id)
        ->where('status', 'Paid') 
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

        return view('staff.payroll.my_payslips', compact('payrolls'));
    }
}
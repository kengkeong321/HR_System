<?php
//Dephnie Ong Yan Yee
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $configs = DB::table('payroll_configs')->get();
        return view('admin.payroll.settings', compact('configs'));
    }

    public function update(Request $request)
    {
        foreach ($request->configs as $key => $value) {
            DB::table('payroll_configs')
                ->where('config_key', $key)
                ->update(['config_value' => $value]);
        }

        return back()->with('success', 'Statutory rates updated for all future calculations.');
    }
}
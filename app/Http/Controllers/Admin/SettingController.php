<?php
// Mu Jun Yi
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $startTime = DB::table('settings')->where('key_name', 'work_start_time')->value('key_value') ?? '09:00';
        
        return view('admin.settings.index', compact('startTime'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required',
        ]);

        DB::table('settings')
            ->updateOrInsert(
                ['key_name' => 'work_start_time'],
                ['key_value' => $request->work_start_time]
            );

        return back()->with('success', 'Work start time updated successfully!');
    }
}
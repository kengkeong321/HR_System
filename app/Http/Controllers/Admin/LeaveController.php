<?php
// Mu Jun Yi
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function staffIndex()
    {
        $userId = session('user_id');
        // Fetch personal history (Access Control: [89])
        $leaves = DB::table('leave_table')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('staff.leave.index', compact('leaves'));
    }

    // STAFF: Submit Request (Data Protection: [138])
    public function store(Request $request)
    {
        // Input Validation: [11] 
        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::table('leave_table')->insert([
            'user_id' => session('user_id'),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'Pending', 
            'created_at' => now(),
        ]);

        return back()->with('success', 'Leave request submitted!');
    }

   public function adminIndex()
    {
        $leaves = DB::table('leave_table')
            ->join('user', 'leave_table.user_id', '=', 'user.user_id')
            ->select('leave_table.*', 'user.user_name as staff_name')
            ->orderBy('leave_table.created_at', 'desc')
            ->get();
            
        return view('admin.leave.index', compact('leaves'));
    }

    // 2. Admin updates the state (Access Control [89])
    public function adminUpdate(Request $request, $id)
    {
        // Input Validation: [11] - Validate for expected ENUM data types
        $request->validate([
            'status' => 'required|in:Approved,Rejected'
        ]);

        DB::table('leave_table')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        return back()->with('success', 'Leave request has been ' . $request->status);
    }
}
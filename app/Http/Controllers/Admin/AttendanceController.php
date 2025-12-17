<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Fix for the 'Log' error:
use Illuminate\Support\Facades\Log; 
// Fix for the 'Auth' error:
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{

    public function index()
    {
        // Admin Check: Observer viewing all logs
        $attendances = Attendance::with('user')->orderBy('attendance_date', 'desc')->get();
        return view('admin.attendance.index', compact('attendances'));
    }

   public function create(Request $request)
    {
        $search = $request->input('search');
        
        // FIX: Always initialize as an empty array, not null
        $users = []; 

        if ($search) {
            $users = \App\Models\User::where('user_name', 'LIKE', "%{$search}%")
                ->where('status', 'Active')
                ->where('role', 'Staff')
                ->get();
        }

        // Pass the variable to the view
        return view('admin.attendance', compact('users', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Validation [11]: Ensure the user exists AND has the Staff role
            'user_id' => 'required|exists:user,user_id',
            'status' => 'required|string|in:Present,Absent,Late',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Extra Security Check [89]
        $targetUser = \App\Models\User::find($validated['user_id']);
        if ($targetUser->role !== 'Staff') {
            return redirect()->back()->withErrors(['error' => 'You can only mark attendance for staff members.']);
        }

        $attendance = new \App\Models\Attendance();
        $attendance->user_id = $validated['user_id'];
        $attendance->status = $validated['status'];
        $attendance->attendance_date = now()->toDateString();
        $attendance->clock_in_time = now()->toTimeString();
        $attendance->remarks = $request->remarks;
        $attendance->save();

        Log::info("Observer Notification: Admin marked attendance for Staff: " . $targetUser->user_name);

        return redirect()->back()->with('success', 'Attendance for ' . $targetUser->user_name . ' marked successfully!');
    }
}
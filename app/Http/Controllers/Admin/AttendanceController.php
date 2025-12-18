<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{

    public function index(Request $request)
    {
        // 1. Initialize query with user relationship
        $query = Attendance::with('user');

        // 2. Filter by User ID (passed from the 'Verify' link in Payroll)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 3. Filter by Month (passed from Payroll, e.g., 'June')
        if ($request->has('month')) {
            // Convert month name to number (e.g., 'June' becomes 6)
            $monthNumber = date('m', strtotime($request->month));
            $query->whereMonth('attendance_date', $monthNumber);
        }

        // 4. Fetch the results (keeping your original ordering)
        $attendances = $query->orderBy('attendance_date', 'desc')->get();

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
            'user_id' => 'required|exists:user,user_id',
            'status' => 'required|string|in:Present,Absent,Late',
            'remarks' => 'nullable|string|max:255',
            'action_type' => 'required|in:in,out'
        ]);

        $today = now()->toDateString();

        // Check if a record already exists for this user today
        $existingRecord = \App\Models\Attendance::where('user_id', $validated['user_id'])
            ->where('attendance_date', $today)
            ->first();

        // LOGIC FOR CLOCK IN
        if ($request->action_type == 'in') {
            if ($existingRecord) {
                // Error Notification: Already Clocked In
                return redirect()->back()
                    ->with('error', 'You have already clocked in today!')
                    ->withInput();
            }

            $attendance = new \App\Models\Attendance();
            $attendance->user_id = $validated['user_id'];
            $attendance->status = $validated['status'];
            $attendance->attendance_date = $today;
            $attendance->clock_in_time = now()->toTimeString();
            $attendance->remarks = $request->remarks;
            $attendance->save();

            return redirect()->back()->with('success', 'Attendance marked successfully!');
        }

        // LOGIC FOR CLOCK OUT
        if ($request->action_type == 'out') {
            if (!$existingRecord) {
                return redirect()->back()
                    ->with('error', 'No clock-in record found for today. Please clock in first!')
                    ->withInput();
            }

            if ($existingRecord->clock_out_time) {
                // Error Notification: Already Clocked Out
                return redirect()->back()
                    ->with('error', 'You have already clocked out today!')
                    ->withInput();
            }

            $existingRecord->update([
                'clock_out_time' => now()->toTimeString(),
                'remarks' => $existingRecord->remarks . " | " . ($request->remarks ?? 'Clocked out')
            ]);

            return redirect()->back()->with('success', 'Clocked out successfully!');
        }
    }

    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('admin.attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        // Input Validation [11]
        $validated = $request->validate([
            'status' => 'required|string|in:Present,Absent,Late',
            'remarks' => 'nullable|string|max:255',
            'attendance_date' => 'required|date',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update($validated);

        // Observer Pattern: Log the manual correction by Admin
        Log::info("Observer Notification: Admin edited Attendance ID " . $id . " for User " . $attendance->user_id);

        return redirect()->route('admin.attendance.index')->with('success', 'Record updated successfully!');
    }

    public function staffCreate()
    {
        $userId = session('user_id');
        $today = now()->toDateString();

        // Fetch today's record to control the buttons
        $attendance = \App\Models\Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        // Fetch the last 5 records for the history table
        $history = \App\Models\Attendance::where('user_id', $userId)
            ->orderBy('attendance_date', 'desc')
            ->take(5)
            ->get();

        return view('staff.attendance', compact('attendance', 'history'));
    }

    public function staffStore(Request $request)
    {
        $userId = session('user_id');
        $today = now()->toDateString();
        $currentTime = now()->toTimeString(); // Captures current time, e.g., "09:15:00"
        $action = $request->input('action_type');

        // 1. Find if a record exists for today to prevent duplicates
        $record = \App\Models\Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if ($action == 'in') {
            if ($record) {
                return back()->with('error', 'You have already clocked in today!');
            }

            // 2. Fetch the Standard Work Start Time from your settings table
            $startTimeSetting = DB::table('settings')
                ->where('key_name', 'work_start_time')
                ->value('key_value');

            // Fallback default if setting is missing to prevent errors
            if (!$startTimeSetting) {
                $startTimeSetting = '09:00:00';
            }

            // 3. Automated Logic: Compare current time to start time
            // If currentTime is LATER than startTimeSetting, status becomes 'Late'
            $status = (strtotime($currentTime) > strtotime($startTimeSetting)) ? 'Late' : 'Present';

            \App\Models\Attendance::create([
                'user_id' => $userId,
                'attendance_date' => $today,
                'clock_in_time' => $currentTime,
                'status' => $status, // Dynamic status based on logic
                'remarks' => ($status === 'Late') ? 'Auto-marked: Late arrival' : null
            ]);

            $message = ($status === 'Late') 
                ? 'Clock-in recorded. You are marked as Late.' 
                : 'Clock-in successful! Have a great day.';

            if ($status === 'Late') {
                // Send a warning instead of success for late users
                return back()->with('warning', 'Clock-in recorded. You are marked as Late!');
            }

            return back()->with('success', $message);
            
        }

        if ($action == 'out') {
            if (!$record) {
                return back()->with('error', 'No clock-in record found. Please clock in first!');
            }
            
            if ($record->clock_out_time) {
                return back()->with('error', 'You have already clocked out today!');
            }

            $record->update([
                'clock_out_time' => $currentTime
            ]);

            return back()->with('success', 'Clock-out successful! See you tomorrow.');
        }
        
    }
}

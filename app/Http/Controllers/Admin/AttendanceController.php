<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{

// Add these at the very top of your file


// Update your index method
public function index(Request $request)
{
    // 1. Optimized Eager Loading (reduces database hits)
    $query = \App\Models\Attendance::with('user');

    if ($request->has('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    $attendances = $query->orderBy('attendance_date', 'desc')->get();

    // 2. Optimized Service Consumption with Cache
    // This stores the position names for 10 minutes so it doesn't call the API every time
    $posMap = Cache::remember('positions_map', 600, function () {
        try {
            // Set a 3-second timeout so the page doesn't hang forever
            $response = Http::timeout(3)->get(url('/api/positions'), [
                'requestID' => 'REQ-' . time(),
                'timeStamp' => now()->format('Y-m-d H:i:s')
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['status']) && $json['status'] === 'ok') {
                    return collect($json['data'])->pluck('name', 'position_id')->toArray();
                }
            }
        } catch (\Exception $e) {
            return []; // Return empty if teammate's API is down
        }
        return [];
    });

    return view('admin.attendance.index', compact('attendances', 'posMap'));
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
        // Access Control [89]: Use session to ensure staff only mark their own attendance
        $userId = session('user_id'); 
        $today = now()->toDateString();
        $currentTime = now()->toTimeString();
        $action = $request->input('action_type');

        // 1. Prevent Duplicates: Check if a record exists for today
        $record = \App\Models\Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        // LOGIC FOR CLOCK IN
        if ($action == 'in') {
            if ($record) {
                return back()->with('error', 'You have already clocked in today!');
            }

            /** * OBSERVER PATTERN IMPLEMENTATION:
             * We save the status as 'Present' by default. 
             * Once 'create' is called, the AttendanceObserver will automatically 
             * check the time and update the status to 'Late' if necessary.
             */
            \App\Models\Attendance::create([
                'user_id' => $userId,
                'attendance_date' => $today,
                'clock_in_time' => $currentTime,
                'status' => 'Present', // The Observer will override this if late
            ]);

            return back()->with('success', 'Clock-in recorded successfully!');
        }

        // LOGIC FOR CLOCK OUT
        if ($action == 'out') {
            if (!$record) {
                return back()->with('error', 'No clock-in record found. Please clock in first!');
            }
            
            if ($record->clock_out_time) {
                return back()->with('error', 'You have already clocked out today!');
            }

            // Data Protection [138]: Updating via server-side logic, not URL parameters
            $record->update([
                'clock_out_time' => $currentTime
            ]);

            return back()->with('success', 'Clock-out successful! See you tomorrow.');
        }
    }

    public function getPositionsFromTeammate()
    {
        // Mandatory Parameters for tracking
        $requestId = 'REQ-' . time();
        $timestamp = now()->format('Y-m-d H:i:s');

        try {
            // Consuming the web service provided by your teammate
            $response = Http::get(url('/api/positions'), [
                'requestID' => $requestId,
                'timeStamp' => $timestamp
            ]);

            if ($response->successful()) {
                $json = $response->json();
                // Validate the status field as per IFA agreement
                if ($json['status'] === 'ok') {
                    return $json['data']; 
                }
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

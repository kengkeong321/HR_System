<?php
//Mu Jun Yi
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

public function index(Request $request)
{
    $query = \App\Models\Attendance::with(['user.staff']); 

    if ($request->has('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    $attendances = $query->orderBy('attendance_date', 'desc')->get();
    $posMap = Cache::remember('positions_map', 600, function () {
        try {
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
            return []; 
        }
        return [];
    });

    return view('admin.attendance.index', compact('attendances', 'posMap'));
}

    public function create(Request $request)
    {
        $search = $request->input('search');

        $users = [];

        if ($search) {
            $users = \App\Models\User::where('user_name', 'LIKE', "%{$search}%")
                ->where('status', 'Active')
                ->whereIn('role', ['Staff', 'HR', 'Finance'])
                ->get();
        }

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

        $existingRecord = \App\Models\Attendance::where('user_id', $validated['user_id'])
            ->where('attendance_date', $today)
            ->first();

        // LOGIC FOR CLOCK IN
        if ($request->action_type == 'in') {
            if ($existingRecord) {
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

        if ($request->action_type == 'out') {
            if (!$existingRecord) {
                return redirect()->back()
                    ->with('error', 'No clock-in record found for today. Please clock in first!')
                    ->withInput();
            }

            if ($existingRecord->clock_out_time) {
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
        $validated = $request->validate([
            'status' => 'required|string|in:Present,Absent,Late',
            'remarks' => 'nullable|string|max:255',
            'attendance_date' => 'required|date',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update($validated);

        Log::info("Observer Notification: Admin edited Attendance ID " . $id . " for User " . $attendance->user_id);

        return redirect()->route('admin.attendance.index')->with('success', 'Record updated successfully!');
    }

    public function staffCreate()
    {
        $userId = session('user_id');
        $today = now()->toDateString();

        $attendance = \App\Models\Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

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

        $record = \App\Models\Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if ($action == 'in') {
            if ($record) {
                return back()->with('error', 'You have already clocked in today!');
            }

            \App\Models\Attendance::create([
                'user_id' => $userId,
                'attendance_date' => $today,
                'clock_in_time' => $currentTime,
                'status' => 'Present', 
            ]);

            return back()->with('success', 'Clock-in recorded successfully!');
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

    public function getPositionsFromTeammate()
    {
        $requestId = 'REQ-' . time();
        $timestamp = now()->format('Y-m-d H:i:s');

        try {
            $response = Http::get(url('/api/positions'), [
                'requestID' => $requestId,
                'timeStamp' => $timestamp
            ]);

            if ($response->successful()) {
                $json = $response->json();
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Department;
use App\Models\User; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $staffs = \App\Models\Staff::paginate(10);
        return view('admin.staff.index', compact('staffs'));
    }

    public function page(Request $request)
    {
        $staffs = \App\Models\Staff::paginate(10);
        return view('admin.staff._list', compact('staffs'))->render();
    }

    public function create()
    {
        $departments = \App\Models\Department::all();
        
        // 1. Fetch the positions from the database
        $positions = \App\Models\Position::where('status', 'Active')->get();

        // 2. Pass them to the view using compact
        return view('admin.staff.create', compact('departments', 'positions'));
    }

        public function checkName(Request $request)
    {
        $exists = \App\Models\Staff::where('full_name', $request->name)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255|unique:staff,full_name', // Ensure unique name
            'email' => 'required|email|unique:staff,email',
            'depart_id'       => 'nullable',
            'position'        => 'nullable',
            'employment_type' => 'required|in:Full-Time,Part-Time,Contract,Intern',
            'join_date'       => 'required|date',
            'basic_salary'    => 'required|numeric',
            'position' => 'nullable|string|exists:positions,name', 
        ]);

        try {
            return DB::transaction(function () use ($request, $validatedData) {
                
                // 2. Auto-Create the User account
                $username = explode('@', $request->email)[0];
                
                $user = \App\Models\User::create([
                    'user_name' => $username,
                    'password'  => hash('sha256', '12345678'), // Default password
                    'role'      => 'Staff',
                    'status'    => 'Active',
                ]);

                // 3. Inject the NEW user_id into the staff data
                // We use $request->all() to get non-validated fields like bank details,
                // then override/add the user_id.
                $staffData = $request->all();
                $staffData['user_id'] = $user->user_id; 

                // 4. Create the Staff record
                \App\Models\Staff::create($staffData);

                return redirect()->route('admin.staff.index')
                                ->with('success', 'Staff and User account created successfully!');
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Creation Failed: ' . $e->getMessage()]);
        }
    }

    public function checkEmail(Request $request)
    {
        // We check the 'staff' table for the email
        $exists = \App\Models\Staff::where('email', $request->query('email'))->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $staff = \App\Models\Staff::findOrFail($id);
        $departments = \App\Models\Department::all();
        
        // Don't forget to add this here too!
        $positions = \App\Models\Position::where('status', 'Active')->get();

        return view('admin.staff.edit', compact('staff', 'departments', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'position'        => 'nullable|string|max:50',
            'depart_id'       => 'nullable|string|max:5',
            'employment_type' => 'required|in:Full-Time,Part-Time,Contract,Intern',
            'basic_salary'    => 'required|numeric',
            'hourly_rate'     => 'nullable|numeric',
            'status'          => 'required|in:Active,Inactive', 
            'position'        => 'nullable|string|exists:position,name', 
        ]);

        try {
            DB::transaction(function () use ($request, $staff) {
                // 1. Update Staff details
                $staff->update($request->all());

                // 2. Update User status using the user_id link
                if ($staff->user_id) {
                    User::where('user_id', $staff->user_id)->update([
                        'status' => $request->status
                    ]);
                }
            });

            return redirect()->route('admin.staff.index')->with('success', 'Staff updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Update Failed: ' . $e->getMessage()]);
        }
    }

}

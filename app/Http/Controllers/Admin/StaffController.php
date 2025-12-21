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
        
        $positions = \App\Models\Position::where('status', 'Active')->get();

        return view('admin.staff.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'full_name'       => 'required|string',
            'email'           => 'required|email|unique:staff,email',
            'depart_id'       => 'nullable',
            'position'        => 'nullable',
            'employment_type' => 'required|in:Full-Time,Part-Time,Contract,Intern',
            'join_date'       => 'required|date',
            'basic_salary'    => 'required|numeric',
            'position' => 'nullable|string|exists:positions,name', 
        ]);

        try {
            return DB::transaction(function () use ($request, $validatedData) {
                
                $username = explode('@', $request->email)[0];
                
                $user = \App\Models\User::create([
                    'user_name' => $username,
                    'password'  => hash('sha256', '12345678'),
                    'role'      => 'Staff',
                    'status'    => 'Active',
                ]);

                $staffData = $request->all();
                $staffData['user_id'] = $user->user_id; 

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
        $exists = \App\Models\Staff::where('email', $request->query('email'))->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $staff = \App\Models\Staff::findOrFail($id);
        $departments = \App\Models\Department::all();

        $positions = \App\Models\Position::where('status', 'Active')->get();

        return view('admin.staff.edit', compact('staff', 'departments', 'positions'));
    }

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
                $staff->update($request->all());

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
    public function destroy(string $id)
    {

    }
}

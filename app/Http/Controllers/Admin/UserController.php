<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $page = null;
        if ($request->has('page') && $request->query('from_nav') === '1') {
            $page = (int) $request->query('page');
            session(['admin.users.page' => $page]);
        } elseif (session()->has('admin.users.page')) {
            $page = session('admin.users.page');
        }

        $users = is_null($page) ? User::paginate(10) : User::paginate(10, ['*'], 'page', $page);
        return view('admin.users.index', compact('users'));
    }

    public function page(Request $request)
    {
        $page = (int) $request->input('page', 1);
        session(['admin.users.page' => $page]);
        $users = User::paginate(10, ['*'], 'page', $page);
        return view('admin.users._list', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_name'    => 'required|unique:user,user_name',
            'password'     => 'required|min:6',
            'full_name'    => 'required|string|max:100',
            'email'        => 'required|email|unique:staff,email',
            'role'         => 'required',
            'basic_salary' => 'numeric',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Create User
            $user = User::create([
                'user_name' => $request->user_name,
                'password'  => hash('sha256', $request->password),
                'role'      => $request->role,
                'status'    => $request->status,
            ]);

            // 2. Create Staff info for EVERYONE
            $isHourly = in_array($request->employment_type, ['Part-Time', 'Intern']);

            Staff::create([
                'user_id'         => $user->user_id,
                'full_name'       => $request->full_name,
                'email'           => $request->email,
                'employment_type' => $request->employment_type,
                'basic_salary'    => !$isHourly ? $request->basic_salary : 0,
                'hourly_rate'     => $isHourly ? $request->basic_salary : 0,
                'join_date'       => now(),
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', 'User and Profile created!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'user_name' => 'required|string|max:50|unique:User,user_name,'.$user->user_id.',user_id',
            'password' => 'nullable|string|min:6',
            'role'      => 'required|in:Admin,Staff,HR,Finance',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = hash('sha256', $data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        // Delete disabled — prefer status toggle
        return redirect()->route('admin.users.index')->with('error', 'Delete operation is disabled. Use status to set Inactive');
    }

    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'Active' ? 'Inactive' : 'Active';
        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'User status updated');
    }
}

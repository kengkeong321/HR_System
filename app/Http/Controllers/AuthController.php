<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('user_name', $request->input('user_name'))
            ->where('status', 'Active')
            ->first();

        if (! $user || ! $user->verifyPassword($request->input('password'))) {
            return back()->withErrors(['credentials' => 'Invalid username or password'])->withInput();
        }

        // store minimal session
        session(["user_id" => $user->user_id, "user_name" => $user->user_name, "role" => $user->role]);

        // Allow both Admin and Staff roles to access the admin dashboard
        if (in_array($user->role, ['Admin', 'Staff'])) {
            return redirect()->route('admin.dashboard');
        }

        // Other roles (if any) are not allowed
        return redirect()->route('login')->withErrors(['access' => 'You do not have access']);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}

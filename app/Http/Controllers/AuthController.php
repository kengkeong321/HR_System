<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Auth::login($user);

        session(["user_id" => $user->user_id, "user_name" => $user->user_name, "role" => $user->role]);

        if (in_array($user->role, ['Admin', 'HR', 'Finance'])) {
            return redirect()->route('admin.dashboard');
        }elseif ($user->role === 'Staff') {
            return redirect()->route('staff.dashboard');
        }

        return redirect()->route('login')->withErrors(['access' => 'You do not have access']);
    }

    public function logout(Request $request)
    {
        // 4. FIX: Use official Logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
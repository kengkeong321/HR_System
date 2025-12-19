<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsStaffOnly
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->session()->get('user_id');

        //handle not logged in
        if (! $userId) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['auth' => 'Please login first.']);
        }
    
        //handle not staff
        $user = User::find($userId);
        
        if ($user->role !== 'Staff') {
            if ($user->role === 'Admin') {
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['access' => 'Staff access required']);
            }

            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['access' => 'Staff access required']);
        }

        return $next($request);
    }
}

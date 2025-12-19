<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->session()->get('user_id');

        // 1. Handle not logged in
        if (!$userId) {
            $this->clearSession($request);
            return redirect()->route('login')->withErrors(['auth' => 'Please login first.']);
        }

        $user = User::find($userId);

        // 2. Updated: Allow Admin, HR, and Finance
        $allowedRoles = ['Admin', 'HR', 'Finance'];

        if (!$user || !in_array($user->role, $allowedRoles)) {
            // If the user exists but their role isn't in our list, block them
            // Note: You might NOT want to clearSession here if you want them to stay 
            // logged in as Staff but just deny them this specific page.
            return redirect()->route('login')->withErrors(['access' => 'Authorized personnel only.']);
        }

        return $next($request);
    }

    private function clearSession(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
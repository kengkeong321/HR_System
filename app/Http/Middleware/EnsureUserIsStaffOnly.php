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

        // Not logged in
        if (! $userId) {
            return $this->logoutAndRedirectToLogin($request, 'Please login first.');
        }

        // Find user
        $user = User::find($userId);
        if (! $user) {
            return $this->logoutAndRedirectToLogin($request, 'Please login first.');
        }

        // Not staff
        if ($user->role !== 'Staff') {
            return $this->logoutAndRedirectToLogin($request, 'Staff access required', 'access');
        }

        return $next($request);
    }

    // Helper to logout and redirect to login with error
    protected function logoutAndRedirectToLogin(Request $request, string $message, string $key = 'auth')
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([$key => $message]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->session()->get('user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user || $user->role !== 'Admin') {
            // Redirect to admin dashboard with error for non-admins
            return redirect()->route('admin.dashboard')->withErrors(['access' => 'Admin access required']);
        }

        return $next($request);
    }
}

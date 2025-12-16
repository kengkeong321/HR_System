<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->session()->get('user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        // Allow both Admin and Staff to access admin pages. Specific admin-only
        // checks (for example user management) should use a stricter middleware.
        if (! $user || ! in_array($user->role, ['Admin', 'Staff'])) {
            return redirect()->route('login')->withErrors(['access' => 'Admin or Staff access required']);
        }

        return $next($request);
    }
}

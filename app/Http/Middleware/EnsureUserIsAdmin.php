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

        $allowedRoles = ['Admin', 'Staff', 'HR', 'Finance'];

        if (! $user || ! in_array($user->role, ['Admin', 'Staff', 'HR', 'Finance'])) {
            return redirect()->route('login')->withErrors(['access' => 'Admin or Staff access required']);
        }

        return $next($request);
    }
}

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

        //handle not logged in
        if (! $userId) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['auth' => 'Please login first.']);
        }

        //handle not admin
        $user = User::find($userId);

        if (! $user || ! in_array($user->role, ['Admin'])) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

        if (! $user || ! in_array($user->role, ['Admin', 'Staff', 'HR', 'Finance'])) {
            return redirect()->route('login')->withErrors(['access' => 'Admin or Staff access required']);
        }

        return $next($request);
    }
}

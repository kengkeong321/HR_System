<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = null;
        if ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
        }

        return view('admin.dashboard', ['user' => $user]);
    }
}

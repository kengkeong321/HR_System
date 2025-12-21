<?php
//Dephnie Ong Yan Yee
namespace App\Policies;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return in_array($user->role, ['Admin', 'HR', 'Finance']);
    }


    public function view(User $user, Payroll $payroll)
    {
        if (in_array($user->role, ['Admin', 'HR', 'Finance'])) {
            return true;
        }

    
        return $user->user_id === $payroll->staff->user_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }


    public function update(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }


    public function delete(User $user)
    {
        return $user->role === 'Admin';
    }

    public function approveL1(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }


    public function approveL2(User $user)
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    public function reject(User $user)
    {
        return in_array($user->role, ['Admin', 'HR', 'Finance']);
    }

    public function export(User $user)
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }
}

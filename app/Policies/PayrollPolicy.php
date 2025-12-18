<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any payrolls.
     */
    public function viewAny(User $user)
    {
        return in_array($user->role, ['Admin', 'HR', 'Finance']);
    }

    /**
     * Determine whether the user can view the payroll.
     */
    public function view(User $user, Payroll $payroll)
    {
        // 1. Management roles can view any record
        if (in_array($user->role, ['Admin', 'HR', 'Finance'])) {
            return true;
        }

        // 2. Ownership Verification: Staff can only see their own context
        // Check if the authenticated user's ID matches the staff record owner
        return $user->user_id === $payroll->staff->user_id;
    }

    /**
     * Determine whether the user can create payrolls.
     */
    public function create(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }

    /**
     * Determine whether the user can update the payroll.
     */
    public function update(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }

    /**
     * Determine whether the user can delete the payroll.
     */
    public function delete(User $user)
    {
        return $user->role === 'Admin';
    }

    /**
     * Determine whether the user can approve Level 1 (HR).
     */
    public function approveL1(User $user)
    {
        return in_array($user->role, ['Admin', 'HR']);
    }

    /**
     * Determine whether the user can approve Level 2 (Finance).
     */
    public function approveL2(User $user)
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can reject batches.
     */
    public function reject(User $user)
    {
        return in_array($user->role, ['Admin', 'HR', 'Finance']);
    }

    /**
     * Determine whether the user can export reports.
     */
    public function export(User $user)
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }
}

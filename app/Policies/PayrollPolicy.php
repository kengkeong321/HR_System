<?php

namespace App\Policies;

use App\Models\User;

class PayrollPolicy
{
    /**
     * Determine if the user can process payroll.
     * Centralized Access Check [78]
     */
    public function process(User $user)
    {
        // Only HR Managers can run payroll
        return $user->hasRole('hr_manager');
    }
}
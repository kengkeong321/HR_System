<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Claim;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClaimPolicy
{
    use HandlesAuthorization;

    /**
     * [78] Single site-wide component to check verify authorization.
     * Restricts actions to HR and Admin only.
     */
    public function verify(User $user)
    {
        return in_array($user->role, ['HR', 'Admin']);
    }

    /**
     * [78] Access Control for viewing receipts and details.
     * [12] Validates ownership range.
     */
    public function view(User $user, Claim $claim)
    {
        // Managers can view all claims
        if (in_array($user->role, ['HR', 'Admin', 'Finance'])) {
            return true;
        }

        // [12] Staff can only view their own claims
        // Use the staff relationship to check the linked user_id
        return $user->user_id === ($claim->staff->user_id ?? null);
    }

    /**
     * Only Admin can perform deletions.
     */
    public function delete(User $user)
    {
        return $user->role === 'Admin';
    }

    /**
     * Staff can only update if it belongs to them and is still Pending.
     */
    public function update(User $user, Claim $claim)
    {
        $isOwner = $user->user_id === ($claim->staff->user_id ?? null);
        return $isOwner && $claim->status === 'Pending';
    }
}
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Claim;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClaimPolicy
{
    use HandlesAuthorization;

    public function verify(User $user, Claim $claim)
    {

        return in_array($user->role, ['HR', 'Admin']);
    }

    public function view(User $user, Claim $claim)
    {
        if (in_array($user->role, ['HR', 'Admin', 'Finance'])) {
            return true;
        }

        return $user->user_id === $claim->staff->user_id;
    }

    public function delete(User $user, Claim $claim)
    {
        return $user->role === 'Admin';
    }

    public function update(User $user, Claim $claim)
    {
        return $user->user_id === $claim->staff->user_id && $claim->status === 'Pending';
    }
}
<?php
//Dephnie Ong Yan Yee
namespace App\Policies;

use App\Models\User;
use App\Models\Claim;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClaimPolicy
{
    use HandlesAuthorization;

    public function verify(User $user)
    {
        return in_array($user->role, ['HR', 'Admin']);
    }

    public function view(User $user, Claim $claim)
    {
        if (in_array($user->role, ['HR', 'Admin', 'Finance'])) {
            return true;
        }

        return $user->user_id === ($claim->staff->user_id ?? null);
    }

    public function delete(User $user)
    {
        return $user->role === 'Admin';
    }

    public function update(User $user, Claim $claim)
    {
        $isOwner = $user->user_id === ($claim->staff->user_id ?? null);
        return $isOwner && $claim->status === 'Pending';
    }
}
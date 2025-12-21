<?php

namespace App\States\User;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ActiveState implements UserStatusState
{
    public function canLogin(): bool 
    {
        return true;
    }

    public function getStatusBadge(): string 
    {
        return '<span class="badge bg-success">Active</span>';
    }

    public function handleStatusChange(User $user): void 
    {
        Log::info("User ID {$user->user_id} has been set to ACTIVE.");
    }
}
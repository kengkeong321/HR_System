<?php
//Loong Wei Lim
namespace App\States\User;

use Illuminate\Support\Facades\Log;
use App\Models\User;

class InactiveState implements UserStatusState
{

    public function canLogin(): bool
    {
        return false;
    }

    public function getStatusBadge(): string
    {
        return '<span class="badge bg-secondary">Inactive</span>';
    }

    public function handleStatusChange(User $user): void
    {
        Log::info("User account for {$user->user_name} (ID: {$user->user_id}) has been set to INACTIVE.");
    }
}
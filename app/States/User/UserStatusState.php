<?php

namespace App\States\User;

use App\Models\User;

interface UserStatusState {
    public function canLogin(): bool;
    public function getStatusBadge(): string;
    public function handleStatusChange(User $user): void;
}
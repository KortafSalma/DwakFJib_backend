<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Distributor;

class DistributorPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Distributor $distributor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_DISTRIBUTOR, User::ROLE_ADMIN]);
    }

    public function update(User $user, Distributor $distributor): bool
    {
        return $user->id === $distributor->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Distributor $distributor): bool
    {
        return $user->id === $distributor->user_id || $user->role === User::ROLE_ADMIN;
    }
}

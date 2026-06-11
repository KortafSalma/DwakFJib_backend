<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pharmacy;

class PharmacyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Pharmacy $pharmacy): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_PHARMACY, User::ROLE_ADMIN]);
    }

    public function update(User $user, Pharmacy $pharmacy): bool
    {
        return $user->id === $pharmacy->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Pharmacy $pharmacy): bool
    {
        return $user->id === $pharmacy->user_id || $user->role === User::ROLE_ADMIN;
    }
}

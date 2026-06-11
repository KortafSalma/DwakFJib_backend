<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Medication;

class MedicationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Medication $medication): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_PHARMACY && $user->pharmacy !== null;
    }

    public function update(User $user, Medication $medication): bool
    {
        return $medication->pharmacy->user_id === $user->id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Medication $medication): bool
    {
        return $medication->pharmacy->user_id === $user->id || $user->role === User::ROLE_ADMIN;
    }
}

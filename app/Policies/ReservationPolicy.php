<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id
            || $reservation->pharmacy->user_id === $user->id
            || $user->role === User::ROLE_ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_USER;
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $reservation->pharmacy->user_id === $user->id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}

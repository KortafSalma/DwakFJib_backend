<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Delivery $delivery): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDistributor() && $delivery->distributor->user_id === $user->id) {
            return true;
        }

        if ($user->isPharmacy() && $delivery->order->pharmacy->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isDistributor() || $user->isAdmin();
    }

    public function update(User $user, Delivery $delivery): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDistributor() && $delivery->distributor->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Delivery $delivery): bool
    {
        return $user->isAdmin();
    }
}

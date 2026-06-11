<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $order->pharmacy->user_id === $user->id
            || $order->distributor->user_id === $user->id
            || $user->role === User::ROLE_ADMIN;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_PHARMACY, User::ROLE_DISTRIBUTOR, User::ROLE_ADMIN]);
    }

    public function update(User $user, Order $order): bool
    {
        return $order->pharmacy->user_id === $user->id
            || $order->distributor->user_id === $user->id
            || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}

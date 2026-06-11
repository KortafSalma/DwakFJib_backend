<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notification;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function markAsRead(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}

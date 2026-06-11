<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Message $message): bool
    {
        return $message->conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->role === User::ROLE_ADMIN;
    }
}

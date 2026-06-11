<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conversation;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $conversation->created_by === $user->id || $user->role === User::ROLE_ADMIN;
    }
}

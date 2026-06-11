<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->role === User::ROLE_ADMIN;
    }
}

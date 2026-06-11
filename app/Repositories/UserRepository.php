<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }

    public function paginateUsers(int $perPage = 15, ?string $role = null, ?string $search = null): LengthAwarePaginator
    {
        $builder = $this->model->query();

        if ($role) {
            $builder->where('role', $role);
        }

        if ($search) {
            $builder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $builder->paginate($perPage);
    }

    public function ban(User $user, ?string $reason = null): bool
    {
        return $user->update([
            'is_active' => false,
            'banned_at' => now(),
            'ban_reason' => $reason,
        ]);
    }

    public function unban(User $user): bool
    {
        return $user->update([
            'is_active' => true,
            'banned_at' => null,
            'ban_reason' => null,
        ]);
    }
}

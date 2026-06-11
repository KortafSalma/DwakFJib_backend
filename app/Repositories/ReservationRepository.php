<?php

namespace App\Repositories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReservationRepository extends BaseRepository
{
    public function __construct(Reservation $model)
    {
        parent::__construct($model);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function paginateByUser(int $userId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $builder = $this->model->where('user_id', $userId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->paginate($perPage);
    }

    public function findByPharmacy(int $pharmacyId): Collection
    {
        return $this->model->where('pharmacy_id', $pharmacyId)->get();
    }

    public function paginateByPharmacy(int $pharmacyId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $builder = $this->model->where('pharmacy_id', $pharmacyId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->paginate($perPage);
    }

    public function pending(): Collection
    {
        return $this->model->where('status', 'PENDING')->get();
    }

    public function expired(): Collection
    {
        return $this->model
            ->where('status', 'PENDING')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();
    }
}

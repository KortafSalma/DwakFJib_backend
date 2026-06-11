<?php

namespace App\Repositories;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
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

    public function findByDistributor(int $distributorId): Collection
    {
        return $this->model->where('distributor_id', $distributorId)->get();
    }

    public function paginateByDistributor(int $distributorId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $builder = $this->model->where('distributor_id', $distributorId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->paginate($perPage);
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $order = $this->findOrFail($orderId);
        return $order->update(['status' => $status]);
    }

    public function byStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }
}

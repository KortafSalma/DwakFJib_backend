<?php

namespace App\Repositories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PharmacyRepository extends BaseRepository
{
    public function __construct(Pharmacy $model)
    {
        parent::__construct($model);
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->get();
    }

    public function paginateSearch(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%")
                  ->orWhere('city', 'like', "%{$query}%");
            })
            ->paginate($perPage);
    }

    public function nearby(float $latitude, float $longitude, float $radius = 10): Collection
    {
        return $this->model
            ->nearby($latitude, $longitude, $radius)
            ->get();
    }

    public function paginateNearby(float $latitude, float $longitude, float $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->nearby($latitude, $longitude, $radius)
            ->paginate($perPage);
    }

    public function verified(): Collection
    {
        return $this->model->where('status', 'VERIFIED')->get();
    }

    public function paginateVerified(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('status', 'VERIFIED')->paginate($perPage);
    }
}

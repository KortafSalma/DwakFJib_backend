<?php

namespace App\Repositories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MedicationRepository extends BaseRepository
{
    public function __construct(Medication $model)
    {
        parent::__construct($model);
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('generic_name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
    }

    public function paginateSearch(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->paginate($perPage);
    }

    public function lowStock(?int $threshold = null): Collection
    {
        $threshold = $threshold ?? config('pharmacy.low_stock_threshold', 10);

        return $this->model
            ->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->get();
    }

    public function outOfStock(): Collection
    {
        return $this->model->where('stock', 0)->get();
    }

    public function findByPharmacy(int $pharmacyId): Collection
    {
        return $this->model->where('pharmacy_id', $pharmacyId)->get();
    }

    public function paginateByPharmacy(int $pharmacyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('pharmacy_id', $pharmacyId)->paginate($perPage);
    }

    public function adjustStock(int $id, int $quantity, string $reason): bool
    {
        $medication = $this->findOrFail($id);
        return $medication->update(['stock' => $medication->stock + $quantity]);
    }
}

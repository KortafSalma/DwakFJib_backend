<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(mixed $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(mixed $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function findBy(array $conditions): ?Model
    {
        return $this->model->where($conditions)->first();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    public function delete(mixed $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    public function where(string $column, mixed $value, string $operator = '='): Collection
    {
        return $this->model->where($column, $operator, $value)->get();
    }

    public function firstWhere(string $column, mixed $value): ?Model
    {
        return $this->model->where($column, $value)->first();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(mixed $id): bool
    {
        return $this->model->where($this->model->getKeyName(), $id)->exists();
    }
}

<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            if (method_exists($this, 'filter' . ucfirst($field))) {
                $query = $this->{'filter' . ucfirst($field)}($query, $value);
            } elseif (in_array($field, $this->getFillable())) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query;
    }

    public function scopeSort(Builder $query, ?string $sortBy = null, string $direction = 'desc'): Builder
    {
        if ($sortBy && in_array($sortBy, $this->getFillable())) {
            return $query->orderBy($sortBy, $direction);
        }

        return $query->orderBy('created_at', $direction);
    }
}

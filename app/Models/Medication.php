<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'generic_name',
        'description',
        'dosage',
        'stock',
        'price',
        'category',
        'is_derma',
        'discount_percent',
        'barcode',
        'requires_prescription',
        'expiry_date',
        'batch_number',
        'low_stock_threshold',
        'photo_front',
        'photo_back',
        'photo_left',
        'photo_right',
        'photo_top',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'expiry_date' => 'date',
            'low_stock_threshold' => 'decimal:2',
            'is_derma' => 'boolean',
        ];
    }

    public function getFinalPriceAttribute()
    {
        if ($this->is_derma && $this->discount_percent > 0) {
            return $this->price - ($this->price * $this->discount_percent / 100);
        }
        return $this->price;
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')->where('expiry_date', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
              ->orWhere('description', 'ilike', "%{$term}%")
              ->orWhere('category', 'ilike', "%{$term}%")
              ->orWhere('dosage', 'ilike', "%{$term}%");
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }
}

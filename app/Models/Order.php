<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'distributor_id',
        'order_number',
        'total_amount',
        'status',
        'delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
}

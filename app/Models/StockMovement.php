<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'medication_id',
        'user_id',
        'reservation_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

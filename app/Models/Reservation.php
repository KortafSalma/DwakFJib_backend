<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'PENDING';
    const STATUS_PAID = 'PAID';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_EXPIRED = 'EXPIRED';

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'medication_id',
        'quantity',
        'deposit_amount',
        'prescription_file',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'deposit_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PAID]);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_PENDING);
    }

    public function calculateDeposit(): float
    {
        return $this->medication->price * $this->quantity * 0.5;
    }

    public function markAsExpired()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);

        $this->medication->increment('stock', $this->quantity);

        StockMovement::create([
            'medication_id' => $this->medication_id,
            'user_id' => $this->user_id,
            'reservation_id' => $this->id,
            'type' => 'IN',
            'quantity' => $this->quantity,
            'stock_before' => $this->medication->stock - $this->quantity,
            'stock_after' => $this->medication->stock,
            'reason' => 'Reservation expired - stock restored',
        ]);
    }
}

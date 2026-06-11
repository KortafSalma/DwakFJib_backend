<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'distributor_id',
        'tracking_number',
        'status',
        'carrier',
        'driver_name',
        'driver_phone',
        'shipping_address',
        'shipping_cost',
        'notes',
        'shipped_at',
        'in_transit_at',
        'delivered_at',
        'estimated_delivery',
        'tracking_history',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'shipped_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery' => 'datetime',
            'tracking_history' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'IN_TRANSIT');
    }

    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    public function addTrackingEvent(string $status, ?string $location = null, ?string $note = null): void
    {
        $history = $this->tracking_history ?? [];
        $history[] = [
            'status' => $status,
            'location' => $location,
            'note' => $note,
            'timestamp' => now()->toISOString(),
        ];

        $this->update([
            'tracking_history' => $history,
            'status' => $status,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'in_app_notifications',
        'reservation_notifications',
        'order_notifications',
        'stock_notifications',
        'delivery_notifications',
        'system_notifications',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'in_app_notifications' => 'boolean',
            'reservation_notifications' => 'boolean',
            'order_notifications' => 'boolean',
            'stock_notifications' => 'boolean',
            'delivery_notifications' => 'boolean',
            'system_notifications' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shouldSend(string $type, string $channel = 'in_app'): bool
    {
        $typeMap = [
            'RESERVATION' => 'reservation_notifications',
            'ORDER' => 'order_notifications',
            'STOCK' => 'stock_notifications',
            'DELIVERY' => 'delivery_notifications',
            'SYSTEM' => 'system_notifications',
        ];

        $preferenceKey = $typeMap[$type] ?? null;

        if (!$preferenceKey || !$this->{$preferenceKey}) {
            return false;
        }

        return $channel === 'in_app' ? $this->in_app_notifications : $this->email_notifications;
    }
}

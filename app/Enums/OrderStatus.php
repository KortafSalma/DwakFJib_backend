<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case SHIPPED = 'SHIPPED';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }
}

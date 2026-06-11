<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
    case PICKED_UP = 'PICKED_UP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';
    case FAILED = 'FAILED';
    case RETURNED = 'RETURNED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Assigned',
            self::PICKED_UP => 'Picked Up',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::RETURNED => 'Returned',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

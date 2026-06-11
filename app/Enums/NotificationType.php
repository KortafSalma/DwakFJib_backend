<?php

namespace App\Enums;

enum NotificationType: string
{
    case ALERT = 'ALERT';
    case RESERVATION = 'RESERVATION';
    case ORDER = 'ORDER';
    case DELIVERY = 'DELIVERY';
    case STOCK = 'STOCK';
    case SYSTEM = 'SYSTEM';
    case PAYMENT = 'PAYMENT';
    case MESSAGE = 'MESSAGE';

    public function label(): string
    {
        return match ($this) {
            self::ALERT => 'Alert',
            self::RESERVATION => 'Reservation',
            self::ORDER => 'Order',
            self::DELIVERY => 'Delivery',
            self::STOCK => 'Stock',
            self::SYSTEM => 'System',
            self::PAYMENT => 'Payment',
            self::MESSAGE => 'Message',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

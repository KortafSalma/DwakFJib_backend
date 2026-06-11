<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
            self::EXPIRED => 'Expired',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::PAID]);
    }
}

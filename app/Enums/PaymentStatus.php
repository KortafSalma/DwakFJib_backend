<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

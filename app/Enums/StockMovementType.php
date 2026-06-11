<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case ADJUSTMENT = 'ADJUSTMENT';
    case RETURN = 'RETURN';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Stock In',
            self::OUT => 'Stock Out',
            self::ADJUSTMENT => 'Adjustment',
            self::RETURN => 'Return',
            self::EXPIRED => 'Expired',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function increasesStock(): bool
    {
        return in_array($this, [self::IN, self::RETURN, self::ADJUSTMENT]);
    }

    public function decreasesStock(): bool
    {
        return in_array($this, [self::OUT, self::EXPIRED]);
    }
}

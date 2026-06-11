<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case PHARMACY = 'PHARMACY';
    case DISTRIBUTOR = 'DISTRIBUTOR';
    case USER = 'USER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::PHARMACY => 'Pharmacy',
            self::DISTRIBUTOR => 'Distributor',
            self::USER => 'User',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

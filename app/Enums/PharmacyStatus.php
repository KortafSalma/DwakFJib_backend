<?php

namespace App\Enums;

enum PharmacyStatus: string
{
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case SUSPENDED = 'SUSPENDED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::SUSPENDED => 'Suspended',
            self::REJECTED => 'Rejected',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isActive(): bool
    {
        return $this === self::VERIFIED;
    }
}

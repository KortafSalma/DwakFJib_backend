<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\StockMovement;
use App\Events\LowStockAlert;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public static function adjustStock(
        Medication $medication,
        int $quantity,
        string $type,
        string $reason,
        ?int $userId = null,
        ?int $reservationId = null
    ): Medication {
        return DB::transaction(function () use ($medication, $quantity, $type, $reason, $userId, $reservationId) {
            $stockBefore = $medication->stock;
            $stockAfter = $type === 'IN' ? $stockBefore + $quantity : $stockBefore - $quantity;

            if ($stockAfter < 0) {
                throw new \InvalidArgumentException('Insufficient stock');
            }

            $medication->update(['stock' => $stockAfter]);

            StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'type' => $type,
                'quantity' => abs($quantity),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
            ]);

            $threshold = config('pharmacy.low_stock_threshold', 10);

            if ($stockAfter <= $threshold && $stockAfter > 0) {
                event(new LowStockAlert($medication, $stockAfter, $threshold));
            }

            return $medication->fresh();
        });
    }

    public static function reserveStock(Medication $medication, int $quantity, ?int $userId = null, ?int $reservationId = null): Medication
    {
        return self::adjustStock($medication, $quantity, 'OUT', 'Reserved for order', $userId, $reservationId);
    }

    public static function releaseStock(Medication $medication, int $quantity, ?int $userId = null, ?int $reservationId = null): Medication
    {
        return self::adjustStock($medication, $quantity, 'IN', 'Reservation cancelled - stock released', $userId, $reservationId);
    }

    public static function fulfillStock(Medication $medication, int $quantity, ?int $userId = null): Medication
    {
        return self::adjustStock($medication, $quantity, 'OUT', 'Reservation fulfilled', $userId);
    }

    public static function receiveStock(Medication $medication, int $quantity, ?int $userId = null, ?string $reason = null): Medication
    {
        return self::adjustStock($medication, $quantity, 'IN', $reason ?? 'Stock received from distributor', $userId);
    }

    public static function getLowStockMedications(?int $pharmacyId = null, ?int $threshold = null)
    {
        $threshold = $threshold ?? config('pharmacy.low_stock_threshold', 10);

        $query = Medication::where('stock', '<=', $threshold)
            ->where('stock', '>', 0);

        if ($pharmacyId) {
            $query->where('pharmacy_id', $pharmacyId);
        }

        return $query->with('pharmacy')->get();
    }

    public static function getOutOfStockMedications(?int $pharmacyId = null)
    {
        $query = Medication::where('stock', 0);

        if ($pharmacyId) {
            $query->where('pharmacy_id', $pharmacyId);
        }

        return $query->with('pharmacy')->get();
    }

    public static function getStockSummary(?int $pharmacyId = null): array
    {
        $query = Medication::query();

        if ($pharmacyId) {
            $query->where('pharmacy_id', $pharmacyId);
        }

        return [
            'total_medications' => $query->count(),
            'total_stock' => $query->sum('stock'),
            'low_stock_count' => (clone $query)->where('stock', '<=', config('pharmacy.low_stock_threshold', 10))->where('stock', '>', 0)->count(),
            'out_of_stock_count' => (clone $query)->where('stock', 0)->count(),
            'expiring_soon_count' => (clone $query)->whereNotNull('expiry_date')->where('expiry_date', '<=', now()->addDays(30))->count(),
            'expired_count' => (clone $query)->whereNotNull('expiry_date')->where('expiry_date', '<', now())->count(),
        ];
    }
}

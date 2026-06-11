<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Medication;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class StockService
{
    public static function decrement(Medication $medication, int $quantity, ?int $userId = null, ?int $reservationId = null, string $reason = '')
    {
        return DB::transaction(function () use ($medication, $quantity, $userId, $reservationId, $reason) {
            $stockBefore = $medication->stock;
            $stockAfter = $stockBefore - $quantity;

            if ($stockAfter < 0) {
                throw new \Exception("Insufficient stock for {$medication->name}");
            }

            $medication->decrement('stock', $quantity);

            $movement = StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'type' => 'OUT',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason ?: 'Stock decremented',
            ]);

            self::checkLowStock($medication);

            return $movement;
        });
    }

    public static function increment(Medication $medication, int $quantity, ?int $userId = null, ?int $reservationId = null, string $reason = '')
    {
        return DB::transaction(function () use ($medication, $quantity, $userId, $reservationId, $reason) {
            $stockBefore = $medication->stock;
            $stockAfter = $stockBefore + $quantity;

            $medication->increment('stock', $quantity);

            return StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'type' => 'IN',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason ?: 'Stock incremented',
            ]);
        });
    }

    public static function adjust(Medication $medication, int $newStock, ?int $userId = null, string $reason = '')
    {
        return DB::transaction(function () use ($medication, $newStock, $userId, $reason) {
            $stockBefore = $medication->stock;
            $difference = $newStock - $stockBefore;

            $medication->update(['stock' => $newStock]);

            $type = $difference >= 0 ? 'IN' : 'OUT';

            return StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $userId,
                'type' => 'ADJUSTMENT',
                'quantity' => abs($difference),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'reason' => $reason ?: 'Manual stock adjustment',
            ]);
        });
    }

    public static function markExpired(Medication $medication, ?int $userId = null)
    {
        return DB::transaction(function () use ($medication, $userId) {
            $stockBefore = $medication->stock;

            StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $userId,
                'type' => 'EXPIRED',
                'quantity' => $stockBefore,
                'stock_before' => $stockBefore,
                'stock_after' => 0,
=                'reason' => 'Medication expired',
            ]);

            $medication->update(['stock' => 0]);
        });
    }

    protected static function checkLowStock(Medication $medication)
    {
        if ($medication->isLowStock()) {
            $pharmacy = $medication->pharmacy;

            if ($pharmacy) {
                Notification::create([
                    'user_id' => $pharmacy->user_id,
                    'title' => 'Low Stock Alert',
                    'message' => "{$medication->name} is now at {$medication->stock} units (threshold: {$medication->low_stock_threshold}).",
                    'type' => 'ALERT',
                    'metadata' => [
                        'medication_id' => $medication->id,
                        'current_stock' => $medication->stock,
                    ],
                ]);
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Medication;
use App\Models\StockMovement;
use App\Events\ReservationCreated;
use App\Events\ReservationStatusChanged;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public static function create(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $medication = Medication::findOrFail($data['medication_id']);

            if ($medication->stock < $data['quantity']) {
                throw new \InvalidArgumentException('Insufficient stock for this reservation');
            }

            $deposit = $data['deposit_amount'] ?? ($medication->price * $data['quantity'] * 0.5);

            $reservation = Reservation::create([
                'user_id' => $data['user_id'],
                'pharmacy_id' => $data['pharmacy_id'],
                'medication_id' => $data['medication_id'],
                'quantity' => $data['quantity'],
                'deposit_amount' => $deposit,
                'prescription_file' => $data['prescription_file'] ?? null,
                'status' => Reservation::STATUS_PENDING,
                'expires_at' => now()->addHours(24),
            ]);

            $medication->decrement('stock', $data['quantity']);

            StockMovement::create([
                'medication_id' => $medication->id,
                'user_id' => $data['user_id'],
                'reservation_id' => $reservation->id,
                'type' => 'OUT',
                'quantity' => $data['quantity'],
                'stock_before' => $medication->stock + $data['quantity'],
                'stock_after' => $medication->stock,
                'reason' => 'Reserved by user',
            ]);

            event(new ReservationCreated($reservation));

            return $reservation->load(['user', 'pharmacy', 'medication']);
        });
    }

    public static function confirm(Reservation $reservation): Reservation
    {
        $oldStatus = $reservation->status;

        $reservation->update(['status' => Reservation::STATUS_CONFIRMED]);

        event(new ReservationStatusChanged($reservation, $oldStatus, Reservation::STATUS_CONFIRMED));

        return $reservation->fresh();
    }

    public static function cancel(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $oldStatus = $reservation->status;

            $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

            $reservation->medication->increment('stock', $reservation->quantity);

            StockMovement::create([
                'medication_id' => $reservation->medication_id,
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => 'IN',
                'quantity' => $reservation->quantity,
                'stock_before' => $reservation->medication->stock - $reservation->quantity,
                'stock_after' => $reservation->medication->stock,
                'reason' => 'Reservation cancelled - stock restored',
            ]);

            event(new ReservationStatusChanged($reservation, $oldStatus, Reservation::STATUS_CANCELLED));

            return $reservation->fresh();
        });
    }

    public static function complete(Reservation $reservation): Reservation
    {
        $oldStatus = $reservation->status;

        $reservation->update(['status' => Reservation::STATUS_COMPLETED]);

        event(new ReservationStatusChanged($reservation, $oldStatus, Reservation::STATUS_COMPLETED));

        return $reservation->fresh();
    }

    public static function expire(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $oldStatus = $reservation->status;

            $reservation->update(['status' => Reservation::STATUS_EXPIRED]);

            $reservation->medication->increment('stock', $reservation->quantity);

            StockMovement::create([
                'medication_id' => $reservation->medication_id,
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => 'IN',
                'quantity' => $reservation->quantity,
                'stock_before' => $reservation->medication->stock - $reservation->quantity,
                'stock_after' => $reservation->medication->stock,
                'reason' => 'Reservation expired - stock restored',
            ]);

            event(new ReservationStatusChanged($reservation, $oldStatus, Reservation::STATUS_EXPIRED));

            return $reservation->fresh();
        });
    }

    public static function expireAllExpired(): int
    {
        $expired = Reservation::expired()->get();
        $count = 0;

        foreach ($expired as $reservation) {
            self::expire($reservation);
            $count++;
        }

        return $count;
    }

    public static function getUserReservationsSummary(int $userId): array
    {
        return [
            'total' => Reservation::where('user_id', $userId)->count(),
            'pending' => Reservation::where('user_id', $userId)->where('status', 'PENDING')->count(),
            'confirmed' => Reservation::where('user_id', $userId)->where('status', 'CONFIRMED')->count(),
            'completed' => Reservation::where('user_id', $userId)->where('status', 'COMPLETED')->count(),
            'cancelled' => Reservation::where('user_id', $userId)->where('status', 'CANCELLED')->count(),
            'expired' => Reservation::where('user_id', $userId)->where('status', 'EXPIRED')->count(),
        ];
    }
}

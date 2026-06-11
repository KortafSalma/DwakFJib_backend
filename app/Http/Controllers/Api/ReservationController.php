<?php

namespace App\Http\Controllers\Api;

use App\Models\Reservation;
use App\Models\Medication;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Reservation::with(['user', 'pharmacy', 'medication'])->latest();

        if ($user->role !== User::ROLE_ADMIN) {
            if ($user->role === User::ROLE_PHARMACY) {
                $query->whereHas('pharmacy', fn($q) => $q->where('user_id', $user->id));
            } else {
                $query->where('user_id', $user->id);
            }
        }

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        if (request()->has('pharmacy_id')) {
            $query->where('pharmacy_id', request('pharmacy_id'));
        }

        if (request()->has('active')) {
            $query->active();
        }

        return ReservationResource::collection($query->paginate(10));
    }

    public function store(StoreReservationRequest $request)
    {
        $this->authorize('create', Reservation::class);

        $reservation = DB::transaction(function () use ($request) {
            $medication = Medication::findOrFail($request->medication_id);

            if ($medication->stock < $request->quantity) {
                throw new \Exception('Insufficient stock');
            }

            if ($medication->isExpired()) {
                throw new \Exception('Medication has expired');
            }

            $prescriptionPath = null;
            if ($request->hasFile('prescription')) {
                $prescriptionPath = $request->file('prescription')->store('prescriptions');
            }

            $deposit = $medication->price * $request->quantity * 0.5;
            $expiresAt = now()->addHours(24);

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'pharmacy_id' => $request->pharmacy_id,
                'medication_id' => $request->medication_id,
                'quantity' => $request->quantity,
                'deposit_amount' => $deposit,
                'prescription_file' => $prescriptionPath,
                'status' => Reservation::STATUS_PENDING,
                'expires_at' => $expiresAt,
            ]);

            StockService::decrement(
                $medication,
                $request->quantity,
                $request->user()->id,
                $reservation->id,
                'Reservation created'
            );

            return $reservation;
        });

        AuditService::logCreated($reservation);
        NotificationService::reservationCreated($reservation);

        return response()->json([
            'message' => 'Reservation created successfully',
            'data' => new ReservationResource($reservation->load(['user', 'pharmacy', 'medication'])),
            'expires_at' => $reservation->expires_at,
        ], 201);
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return new ReservationResource(
            $reservation->load(['user', 'pharmacy', 'medication'])
        );
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $oldValues = $reservation->toArray();

        if ($request->has('status') && $request->status === 'PAID') {
            DB::transaction(function () use ($reservation) {
                $reservation->update(['status' => Reservation::STATUS_PAID]);

                NotificationService::reservationApproved($reservation);
            });
        } elseif ($request->has('status') && $request->status === 'CANCELLED') {
            DB::transaction(function () use ($reservation) {
                $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

                StockService::increment(
                    $reservation->medication,
                    $reservation->quantity,
                    $reservation->user_id,
                    $reservation->id,
                    'Reservation cancelled - stock restored'
                );
            });
        } else {
            $reservation->update($request->validated());
        }

        AuditService::logUpdated($reservation, $oldValues, $reservation->toArray());

        return response()->json([
            'message' => 'Reservation updated successfully',
            'data' => new ReservationResource($reservation)
        ]);
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        if (!in_array($reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_PAID])) {
            return response()->json([
                'message' => 'Cannot cancel reservation in current status'
            ], 400);
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

            StockService::increment(
                $reservation->medication,
                $reservation->quantity,
                $reservation->user_id,
                $reservation->id,
                'Reservation cancelled - stock restored'
            );

            AuditService::log('cancelled', $reservation);
        });

        return response()->json([
            'message' => 'Reservation cancelled successfully'
        ]);
    }
}

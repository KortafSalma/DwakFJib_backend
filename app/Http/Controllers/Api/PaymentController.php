<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Payment::with('order')->latest();

        if ($user->role === User::ROLE_PHARMACY) {
            $query->whereHas('order.pharmacy', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === User::ROLE_DISTRIBUTOR) {
            $query->whereHas('order.distributor', fn($q) => $q->where('user_id', $user->id));
        }

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        if (request()->has('payment_method')) {
            $query->where('payment_method', request('payment_method'));
        }

        return PaymentResource::collection($query->paginate(10));
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {
            $order = Order::findOrFail($request->order_id);

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount ?? $order->total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'PENDING',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
            ]);

            if ($request->has('auto_complete')) {
                $payment->update(['status' => 'COMPLETED']);
                $order->update(['status' => 'CONFIRMED']);
            }

            return $payment;
        });

        AuditService::logCreated($payment);

        return response()->json([
            'message' => 'Payment initiated',
            'data' => new PaymentResource($payment)
        ], 201);
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return new PaymentResource(
            $payment->load('order')
        );
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $oldValues = $payment->toArray();
        $oldStatus = $payment->status;

        $payment->update($request->validated());

        if ($oldStatus !== $payment->status && $payment->status === 'COMPLETED') {
            $payment->order->update(['status' => 'CONFIRMED']);

            NotificationService::sendToUser(
                $payment->order->pharmacy->user,
                'Payment Received',
                "Payment for order {$payment->order->order_number} has been completed.",
                'PAYMENT',
                ['payment_id' => $payment->id]
            );
        }

        AuditService::logUpdated($payment, $oldValues, $payment->toArray());

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => new PaymentResource($payment)
        ]);
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        AuditService::logDeleted($payment);

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }
}

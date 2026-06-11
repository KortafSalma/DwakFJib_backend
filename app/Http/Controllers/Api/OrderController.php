<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Order::with(['pharmacy', 'distributor', 'payment'])->latest();

        if ($user->role === User::ROLE_PHARMACY) {
            $query->whereHas('pharmacy', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === User::ROLE_DISTRIBUTOR) {
            $query->whereHas('distributor', fn($q) => $q->where('user_id', $user->id));
        }

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        if (request()->has('pharmacy_id')) {
            $query->where('pharmacy_id', request('pharmacy_id'));
        }

        if (request()->has('distributor_id')) {
            $query->where('distributor_id', request('distributor_id'));
        }

        return OrderResource::collection($query->paginate(10));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            return Order::create([
                'pharmacy_id' => $request->pharmacy_id,
                'distributor_id' => $request->distributor_id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'total_amount' => $request->total_amount,
                'status' => 'PENDING',
                'delivery_date' => $request->delivery_date,
            ]);
        });

        AuditService::logCreated($order);

        NotificationService::sendToUser(
            $order->distributor->user,
            'New Order Received',
            "Order {$order->order_number} from {$order->pharmacy->name} for {$order->total_amount}.",
            'ORDER',
            ['order_id' => $order->id]
        );

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order->load(['pharmacy', 'distributor']))
        ], 201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return new OrderResource(
            $order->load(['pharmacy', 'distributor', 'payment'])
        );
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        $oldValues = $order->toArray();
        $oldStatus = $order->status;

        $order->update($request->validated());

        if ($oldStatus !== $order->status) {
            NotificationService::orderStatusChanged($order, $oldStatus, $order->status);
        }

        AuditService::logUpdated($order, $oldValues, $order->toArray());

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order)
        ]);
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        AuditService::logDeleted($order);

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Delivery::with(['order.pharmacy', 'distributor'])->latest();

        if ($user->role === User::ROLE_DISTRIBUTOR) {
            $query->whereHas('distributor', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === User::ROLE_PHARMACY) {
            $query->whereHas('order.pharmacy', fn($q) => $q->where('user_id', $user->id));
        }

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        if (request()->has('tracking_number')) {
            $query->where('tracking_number', request('tracking_number'));
        }

        return DeliveryResource::collection($query->paginate(15));
    }

    public function store(StoreDeliveryRequest $request)
    {
        $order = Order::findOrFail($request->order_id);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'distributor_id' => $request->distributor_id,
            'tracking_number' => 'TRK-' . strtoupper(uniqid()),
            'status' => 'PENDING',
            'carrier' => $request->carrier,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'notes' => $request->notes,
            'estimated_delivery' => $request->estimated_delivery,
        ]);

        AuditService::logCreated($delivery);

        NotificationService::sendToUser(
            $order->pharmacy->user,
            'Delivery Created',
            "Delivery for order {$order->order_number} has been created.",
            'DELIVERY',
            ['delivery_id' => $delivery->id, 'order_id' => $order->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Delivery created successfully',
            'data' => new DeliveryResource($delivery->load(['order', 'distributor'])),
            'errors' => [],
        ], 201);
    }

    public function show(Delivery $delivery)
    {
        $this->authorize('view', $delivery);

        return response()->json([
            'success' => true,
            'message' => 'Delivery retrieved successfully',
            'data' => new DeliveryResource($delivery->load(['order.pharmacy', 'distributor'])),
            'errors' => [],
        ]);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        $oldStatus = $delivery->status;

        $delivery->update($request->validated());

        if ($oldStatus !== $delivery->status) {
            $delivery->addTrackingEvent($delivery->status);

            NotificationService::sendToUser(
                $delivery->order->pharmacy->user,
                'Delivery Status Updated',
                "Delivery for order {$delivery->order->order_number} is now {$delivery->status}.",
                'DELIVERY',
                ['delivery_id' => $delivery->id]
            );
        }

        AuditService::logUpdated($delivery, ['status' => $oldStatus], ['status' => $delivery->status]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery updated successfully',
            'data' => new DeliveryResource($delivery),
            'errors' => [],
        ]);
    }

    public function updateStatus(Delivery $delivery, UpdateDeliveryRequest $request)
    {
        $this->authorize('update', $delivery);

        $oldStatus = $delivery->status;
        $newStatus = $request->status;

        $delivery->addTrackingEvent($newStatus, $request->location, $request->notes);

        if ($newStatus === 'DELIVERED') {
            $delivery->update(['delivered_at' => now()]);
            $delivery->order->update(['status' => 'DELIVERED']);
        } elseif ($newStatus === 'IN_TRANSIT') {
            $delivery->update(['in_transit_at' => now()]);
        } elseif ($newStatus === 'SHIPPED') {
            $delivery->update(['shipped_at' => now()]);
        }

        NotificationService::sendToUser(
            $delivery->order->pharmacy->user,
            'Delivery Status Updated',
            "Delivery tracking #{$delivery->tracking_number} is now {$newStatus}.",
            'DELIVERY',
            ['delivery_id' => $delivery->id, 'tracking_number' => $delivery->tracking_number]
        );

        return response()->json([
            'success' => true,
            'message' => "Delivery status updated to {$newStatus}",
            'data' => new DeliveryResource($delivery),
            'errors' => [],
        ]);
    }

    public function trackByNumber(string $trackingNumber)
    {
        $delivery = Delivery::where('tracking_number', $trackingNumber)
            ->with(['order.pharmacy', 'distributor'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Tracking information retrieved',
            'data' => new DeliveryResource($delivery),
            'errors' => [],
        ]);
    }

    public function destroy(Delivery $delivery)
    {
        $this->authorize('delete', $delivery);

        AuditService::logDeleted($delivery);

        $delivery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery deleted successfully',
            'data' => null,
            'errors' => [],
        ]);
    }
}

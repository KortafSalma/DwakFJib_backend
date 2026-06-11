<?php

namespace App\Http\Controllers\Api;

use App\Models\Distributor;
use App\Models\Order;
use App\Models\Delivery;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDistributorRequest;
use App\Http\Requests\UpdateDistributorRequest;
use App\Http\Resources\DistributorResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\DeliveryResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{
    public function index()
    {
        $query = Distributor::withCount('orders');

        if (request()->has('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        if (request()->has('city')) {
            $query->where('city', request('city'));
        }

        if (request()->has('active')) {
            $query->where('is_active', request('active') === 'true');
        }

        return response()->json([
            'success' => true,
            'message' => 'Distributors retrieved successfully',
            'data' => DistributorResource::collection($query->latest()->paginate(15)),
            'errors' => [],
        ]);
    }

    public function store(StoreDistributorRequest $request)
    {
        $distributor = DB::transaction(function () use ($request) {
            return Distributor::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_active' => true,
            ]);
        });

        AuditService::logCreated($distributor);

        return response()->json([
            'success' => true,
            'message' => 'Distributor created successfully',
            'data' => new DistributorResource($distributor),
            'errors' => [],
        ], 201);
    }

    public function show(Distributor $distributor)
    {
        return response()->json([
            'success' => true,
            'message' => 'Distributor retrieved successfully',
            'data' => new DistributorResource(
                $distributor->load(['user', 'orders', 'deliveries'])
            ),
            'errors' => [],
        ]);
    }

    public function update(UpdateDistributorRequest $request, Distributor $distributor)
    {
        $this->authorize('update', $distributor);

        $oldValues = $distributor->toArray();

        $distributor->update($request->validated());

        AuditService::logUpdated($distributor, $oldValues, $distributor->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Distributor updated successfully',
            'data' => new DistributorResource($distributor),
            'errors' => [],
        ]);
    }

    public function destroy(Distributor $distributor)
    {
        $this->authorize('delete', $distributor);

        AuditService::logDeleted($distributor);

        $distributor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Distributor deleted successfully',
            'data' => null,
            'errors' => [],
        ]);
    }

    public function orders(Distributor $distributor)
    {
        $this->authorize('view', $distributor);

        $query = $distributor->orders()->with(['pharmacy', 'payment'])->latest();

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $orders = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders),
            'errors' => [],
        ]);
    }

    public function deliveries(Distributor $distributor)
    {
        $this->authorize('view', $distributor);

        $query = $distributor->deliveries()->with(['order.pharmacy'])->latest();

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $deliveries = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Deliveries retrieved successfully',
            'data' => DeliveryResource::collection($deliveries),
            'errors' => [],
        ]);
    }

    public function updateOrderStatus(Distributor $distributor, Order $order)
    {
        $this->authorize('update', $order);

        if ($order->distributor_id !== $distributor->id) {
            return response()->json([
                'success' => false,
                'message' => 'This order does not belong to this distributor',
                'data' => null,
                'errors' => ['order' => ['Order not assigned to this distributor']],
            ], 403);
        }

        $validated = request()->validate([
            'status' => 'required|in:PROCESSING,SHIPPED,IN_TRANSIT,DELIVERED,CANCELLED',
        ]);

        $oldStatus = $order->status;

        $order->update(['status' => $validated['status']]);

        NotificationService::orderStatusChanged($order, $oldStatus, $validated['status']);

        AuditService::logUpdated($order, ['status' => $oldStatus], ['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Order status updated to {$validated['status']}",
            'data' => new OrderResource($order),
            'errors' => [],
        ]);
    }

    public function analytics(Distributor $distributor)
    {
        $this->authorize('view', $distributor);

        return response()->json([
            'success' => true,
            'message' => 'Distributor analytics retrieved',
            'data' => [
                'total_orders' => $distributor->orders()->count(),
                'pending_orders' => $distributor->orders()->where('status', 'PENDING')->count(),
                'in_transit_orders' => $distributor->orders()->where('status', 'IN_TRANSIT')->count(),
                'delivered_orders' => $distributor->orders()->where('status', 'DELIVERED')->count(),
                'total_deliveries' => $distributor->deliveries()->count(),
                'pending_deliveries' => $distributor->deliveries()->where('status', 'PENDING')->count(),
                'completed_deliveries' => $distributor->deliveries()->where('status', 'DELIVERED')->count(),
            ],
            'errors' => [],
        ]);
    }
}

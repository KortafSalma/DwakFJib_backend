<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_number' => $this->whenLoaded('order', $this->order->order_number),
            'distributor' => $this->whenLoaded('distributor', function () {
                return [
                    'id' => $this->distributor->id,
                    'name' => $this->distributor->name,
                ];
            }),
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'carrier' => $this->carrier,
            'driver_name' => $this->driver_name,
            'driver_phone' => $this->driver_phone,
            'shipping_address' => $this->shipping_address,
            'shipping_cost' => $this->shipping_cost,
            'notes' => $this->notes,
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'in_transit_at' => $this->in_transit_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'estimated_delivery' => $this->estimated_delivery?->toIso8601String(),
            'tracking_history' => $this->tracking_history,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

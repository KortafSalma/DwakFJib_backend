<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'orders_count' => $this->whenCounted('orders'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

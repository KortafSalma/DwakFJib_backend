<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_verified' => $this->is_verified,
            'rating' => $this->rating,
            'total_reviews' => $this->total_reviews,
            'logo' => $this->logo,
            'medications_count' => $this->whenCounted('medications'),
            'distance' => $this->when(isset($this->distance), $this->distance),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

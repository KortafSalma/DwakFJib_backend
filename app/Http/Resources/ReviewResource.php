<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_verified_purchase' => $this->is_verified_purchase,
            'user' => new UserResource($this->whenLoaded('user')),
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

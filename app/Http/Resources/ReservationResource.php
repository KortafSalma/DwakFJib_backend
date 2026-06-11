<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'deposit_amount' => $this->deposit_amount,
            'prescription_file' => $this->prescription_file,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'is_expired' => $this->isExpired(),
            'user' => new UserResource($this->whenLoaded('user')),
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'medication' => new MedicationResource($this->whenLoaded('medication')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

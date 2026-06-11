<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'description' => $this->description,
            'dosage' => $this->dosage,
            'stock' => $this->stock,
            'price' => $this->price,
            'category' => $this->category,
            'is_derma' => $this->is_derma,
            'discount_percent' => $this->discount_percent,
            'final_price' => $this->final_price,
            'barcode' => $this->barcode,
            'requires_prescription' => $this->requires_prescription,
            'expiry_date' => $this->expiry_date,
            'batch_number' => $this->batch_number,
            'low_stock_threshold' => $this->low_stock_threshold,
            'photo_front' => $this->photo_front,
            'photo_back' => $this->photo_back,
            'photo_left' => $this->photo_left,
            'photo_right' => $this->photo_right,
            'photo_top' => $this->photo_top,
            'is_expired' => $this->isExpired(),
            'is_low_stock' => $this->isLowStock(),
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

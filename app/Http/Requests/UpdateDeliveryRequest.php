<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('patch') && $this->has('status')) {
            return [
                'status' => 'required|in:PENDING,ASSIGNED,PICKED_UP,IN_TRANSIT,DELIVERED,FAILED,RETURNED',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ];
        }

        return [
            'carrier' => 'sometimes|string|max:255',
            'driver_name' => 'sometimes|string|max:255',
            'driver_phone' => 'sometimes|string|max:20',
            'shipping_address' => 'sometimes|string',
            'shipping_cost' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|string',
            'estimated_delivery' => 'sometimes|date',
            'status' => 'sometimes|in:PENDING,ASSIGNED,PICKED_UP,IN_TRANSIT,DELIVERED,FAILED,RETURNED',
        ];
    }
}

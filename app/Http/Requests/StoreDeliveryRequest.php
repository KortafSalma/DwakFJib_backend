<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'distributor_id' => 'required|exists:distributors,id',
            'carrier' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'estimated_delivery' => 'nullable|date|after:today',
        ];
    }
}

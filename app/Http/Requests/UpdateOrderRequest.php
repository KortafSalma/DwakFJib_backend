<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:PENDING,CONFIRMED,SHIPPED,DELIVERED,CANCELLED',
            'delivery_date' => 'nullable|date',
            'total_amount' => 'sometimes|numeric|min:0',
        ];
    }
}

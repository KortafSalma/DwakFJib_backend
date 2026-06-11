<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'distributor_id' => 'required|exists:distributors,id',
            'total_amount' => 'required|numeric|min:0',
            'delivery_date' => 'nullable|date|after:today',
        ];
    }
}

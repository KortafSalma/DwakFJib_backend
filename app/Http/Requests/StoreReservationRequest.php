<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'medication_id' => 'required|exists:medications,id',
            'quantity' => 'required|integer|min:1',
            'prescription' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}

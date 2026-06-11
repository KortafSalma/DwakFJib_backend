<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:PENDING,PAID,CANCELLED,COMPLETED',
            'quantity' => 'sometimes|integer|min:1',
        ];
    }
}

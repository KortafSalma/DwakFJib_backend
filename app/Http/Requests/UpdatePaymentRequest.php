<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:PENDING,COMPLETED,FAILED,REFUNDED',
            'transaction_id' => 'nullable|string|max:255',
        ];
    }
}

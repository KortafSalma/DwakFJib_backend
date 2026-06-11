<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:CREDIT_CARD,DEBIT_CARD,BANK_TRANSFER,WALLET',
        ];
    }
}

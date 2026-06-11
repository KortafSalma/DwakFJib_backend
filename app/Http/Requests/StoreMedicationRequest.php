<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dosage' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'is_derma' => 'sometimes|boolean',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100|unique:medications,barcode',
            'expiry_date' => 'nullable|date|after:today',
            'batch_number' => 'nullable|string|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'photo_front' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_back' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_left' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_right' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_top' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}

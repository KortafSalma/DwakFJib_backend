<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $medicationId = $this->route('medication');
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'dosage' => 'nullable|string|max:255',
            'stock' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'is_derma' => 'sometimes|boolean',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100|unique:medications,barcode,' . $medicationId,
            'expiry_date' => 'nullable|date',
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

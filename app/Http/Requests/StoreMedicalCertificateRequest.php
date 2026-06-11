<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
        ];
    }
}

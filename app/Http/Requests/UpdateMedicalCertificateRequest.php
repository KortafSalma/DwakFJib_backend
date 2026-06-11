<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:PENDING,VERIFIED,EXPIRED,REJECTED',
            'issue_date' => 'sometimes|date',
            'expiry_date' => 'sometimes|date|after:issue_date',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => 'required_without:attachments|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,csv',
        ];
    }
}

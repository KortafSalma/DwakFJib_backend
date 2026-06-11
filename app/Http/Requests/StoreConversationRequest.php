<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'nullable|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|exists:users,id|different:user_id',
            'message' => 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$userId}",
            'password' => 'sometimes|min:6',
            'role' => 'sometimes|in:ADMIN,PHARMACY,DISTRIBUTOR,USER',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

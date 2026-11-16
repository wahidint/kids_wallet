<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class LockWalletRequest extends FormRequest
{
    /**
     * Permission is already checked by the route's "can:manage-child" middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:150'],
        ];
    }
}
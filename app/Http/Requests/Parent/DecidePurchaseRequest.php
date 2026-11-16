<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class DecidePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route's "can:decide" check already applies
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:200'],
        ];
    }
}
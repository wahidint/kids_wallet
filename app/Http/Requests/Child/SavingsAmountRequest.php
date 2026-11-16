<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

/** Used for both "Save" and "Take out". */
class SavingsAmountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route's "can:manage" check already applies
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Enter an amount.',
            'amount.decimal' => 'Use at most 2 decimal places (poisha).',
        ];
    }
}
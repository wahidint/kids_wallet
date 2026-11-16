<?php

namespace App\Http\Requests\Parent;

use App\Enums\PocketMoneyOccasion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddMoneyRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:10000'],
            'occasion' => ['required', Rule::enum(PocketMoneyOccasion::class)],
            'note' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The amount must be at least ৳1.',
            'amount.max' => 'You can add up to ৳10,000 at a time.',
            'amount.decimal' => 'Use at most 2 decimal places (poisha).',
        ];
    }
}
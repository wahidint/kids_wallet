<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class SpendRequest extends FormRequest
{
    /**
     * The route's "role:child" middleware already ensures a child is logged in,
     * and they can only ever spend from their OWN wallet.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:spending_categories,id'],
            'description' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The amount must be at least ৳1.',
            'amount.decimal' => 'Use at most 2 decimal places (poisha).',
            'category_id.required' => 'Pick a category for this spending.',
            'category_id.exists' => 'Pick one of the categories shown.',
            'description.required' => 'Tell us what you bought.',
        ];
    }
}
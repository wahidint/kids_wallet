<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // "role:child" middleware already applies
    }

    public function rules(): array
    {
        return [
            'item_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:50000'],
            'category_id' => ['required', 'integer', 'exists:spending_categories,id'],
            'description' => ['required', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_name.required' => 'What would you like to buy?',
            'category_id.required' => 'Pick a category.',
            'description.required' => 'Tell your parents why you want it.',
            'amount.decimal' => 'Use at most 2 decimal places (poisha).',
        ];
    }
}
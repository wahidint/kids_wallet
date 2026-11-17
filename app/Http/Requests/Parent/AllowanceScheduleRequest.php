<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class AllowanceScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route's "can:manage-child" check already applies
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:10000'],
            'frequency' => ['required', 'in:weekly,monthly'],
            'day_of_week' => ['exclude_unless:frequency,weekly', 'required', 'integer', 'between:0,6'],
            'day_of_month' => ['exclude_unless:frequency,monthly', 'required', 'integer', 'between:1,28'],
            'note' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'An allowance can be up to ৳10,000.',
            'day_of_week.required' => 'Pick a day of the week.',
            'day_of_month.required' => 'Pick a day of the month.',
        ];
    }
}
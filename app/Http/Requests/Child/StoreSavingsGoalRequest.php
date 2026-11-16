<?php

namespace App\Http\Requests\Child;

use App\Models\SavingsGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // "role:child" middleware already applies
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'icon' => ['required', Rule::in(array_keys(SavingsGoal::ICONS))],
            'target_amount' => ['required', 'numeric', 'decimal:0,2', 'min:10', 'max:100000'],
            'deadline' => ['required', 'date', 'after:today', 'before_or_equal:'.now()->addYears(3)->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'What are you saving for?',
            'icon.required' => 'Pick an icon for your goal.',
            'target_amount.min' => 'A goal should be at least ৳10.',
            'deadline.after' => 'Pick a date in the future.',
            'deadline.before_or_equal' => 'Pick a date within the next 3 years.',
        ];
    }
}
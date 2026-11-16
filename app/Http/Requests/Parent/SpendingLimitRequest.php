<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SpendingLimitRequest extends FormRequest
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
        $amount = ['nullable', 'numeric', 'decimal:0,2', 'min:1', 'max:100000'];

        return [
            'daily_limit' => $amount,
            'weekly_limit' => $amount,
            'monthly_limit' => $amount,
        ];
    }

    /**
     * Extra checks after the rules: limits must make sense together.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $daily = $this->input('daily_limit');
                $weekly = $this->input('weekly_limit');
                $monthly = $this->input('monthly_limit');

                if (filled($daily) && filled($weekly) && (float) $daily > (float) $weekly) {
                    $validator->errors()->add('weekly_limit', 'The weekly limit cannot be smaller than the daily limit.');
                }

                if (filled($weekly) && filled($monthly) && (float) $weekly > (float) $monthly) {
                    $validator->errors()->add('monthly_limit', 'The monthly limit cannot be smaller than the weekly limit.');
                } elseif (filled($daily) && filled($monthly) && (float) $daily > (float) $monthly) {
                    $validator->errors()->add('monthly_limit', 'The monthly limit cannot be smaller than the daily limit.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'daily_limit' => 'daily limit',
            'weekly_limit' => 'weekly limit',
            'monthly_limit' => 'monthly limit',
        ];
    }
}
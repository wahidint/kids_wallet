<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class FinishQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['integer', 'between:0,3'],
        ];
    }
}
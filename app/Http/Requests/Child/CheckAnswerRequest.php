<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class CheckAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // "role:child" middleware already applies
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'answer' => ['required', 'integer', 'between:0,3'],
        ];
    }
}
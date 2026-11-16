<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Clean up the input BEFORE validating it.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
            'link_code' => Str::upper(trim((string) $this->input('link_code'))),
            'phone' => preg_replace('/[\s-]/', '', (string) $this->input('phone')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'regex:/^01[3-9]\d{8}$/'],

            // Child-only fields: ignored completely when role is "parent"
            'date_of_birth' => [
                'exclude_unless:role,child', 'required', 'date', 'before:today',
                'after:'.now()->subYears(16)->toDateString(),
            ],
            'link_code' => [
                'exclude_unless:role,child', 'required', 'string', 'size:6',
                Rule::exists('users', 'link_code')->where('role', UserRole::Parent->value),
            ],

            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Bangladeshi mobile number, e.g. 01712345678.',
            'date_of_birth.after' => config('app.name').' is for children under 16.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'link_code.exists' => 'No parent was found with this code. Ask your parent to check the code on their dashboard.',
            'link_code.size' => 'The family link code has exactly 6 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'link_code' => 'family link code',
            'date_of_birth' => 'date of birth',
        ];
    }
}
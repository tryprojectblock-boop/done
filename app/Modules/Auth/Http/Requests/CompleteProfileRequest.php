<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Rules\StrongPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/', // Only letters, spaces, hyphens, apostrophes
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                new StrongPasswordRule(),
            ],
            'password_confirmation' => [
                'required',
                'same:password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.required' => 'Please enter your last name.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            'password.required' => 'Please create a password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password_confirmation.required' => 'Please confirm your password.',
            'password_confirmation.same' => 'Passwords do not match.',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Rules\WorkEmailRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                new WorkEmailRule(strict: false), // Set to true to block personal emails
                'unique:users,email',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your work email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please sign in instead.',
        ];
    }
}

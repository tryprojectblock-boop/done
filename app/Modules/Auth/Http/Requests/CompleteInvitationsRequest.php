<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInvitationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invited_emails' => ['nullable', 'array', 'max:20'],
            'invited_emails.*' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                'distinct',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'invited_emails.max' => 'You can invite up to 20 team members.',
            'invited_emails.*.email' => 'Please enter a valid email address.',
            'invited_emails.*.distinct' => 'Duplicate email addresses are not allowed.',
        ];
    }

    /**
     * Get the validated invited emails, filtering out empty values.
     */
    public function getValidEmails(): array
    {
        $emails = $this->validated('invited_emails', []);

        return array_values(array_filter($emails, fn ($email) => ! empty($email)));
    }
}

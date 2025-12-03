<?php

declare(strict_types=1);

namespace App\Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkEmailRule implements ValidationRule
{
    /**
     * Free email providers that are not allowed for work email.
     */
    protected array $blockedDomains = [
        'gmail.com',
        'yahoo.com',
        'yahoo.co.uk',
        'hotmail.com',
        'hotmail.co.uk',
        'outlook.com',
        'live.com',
        'msn.com',
        'aol.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'mail.com',
        'protonmail.com',
        'proton.me',
        'yandex.com',
        'yandex.ru',
        'zoho.com',
        'gmx.com',
        'gmx.net',
        'inbox.com',
        'fastmail.com',
        'tutanota.com',
        'mailinator.com',
        'guerrillamail.com',
        'tempmail.com',
        '10minutemail.com',
        'throwaway.email',
    ];

    protected bool $strict;

    public function __construct(bool $strict = false)
    {
        $this->strict = $strict;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        $email = strtolower(trim($value));

        // Basic email format validation
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        // Extract domain
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        $domain = $parts[1];

        // Check if domain is in blocked list (only in strict mode)
        if ($this->strict && in_array($domain, $this->blockedDomains, true)) {
            $fail('Please use your work email address. Personal email addresses like Gmail, Yahoo, or Hotmail are not accepted.');
            return;
        }

        // Check MX records to verify domain can receive emails
        if (! checkdnsrr($domain, 'MX') && ! checkdnsrr($domain, 'A')) {
            $fail('The email domain does not appear to be valid.');
        }
    }
}

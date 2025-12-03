<?php

declare(strict_types=1);

namespace App\Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    protected int $minLength;
    protected bool $requireUppercase;
    protected bool $requireLowercase;
    protected bool $requireNumbers;
    protected bool $requireSpecialChars;
    protected string $specialChars;

    public function __construct(
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecialChars = true,
        string $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?'
    ) {
        $this->minLength = $minLength;
        $this->requireUppercase = $requireUppercase;
        $this->requireLowercase = $requireLowercase;
        $this->requireNumbers = $requireNumbers;
        $this->requireSpecialChars = $requireSpecialChars;
        $this->specialChars = $specialChars;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        $errors = [];

        // Minimum length
        if (strlen($value) < $this->minLength) {
            $errors[] = "at least {$this->minLength} characters";
        }

        // Uppercase requirement
        if ($this->requireUppercase && ! preg_match('/[A-Z]/', $value)) {
            $errors[] = 'one uppercase letter';
        }

        // Lowercase requirement
        if ($this->requireLowercase && ! preg_match('/[a-z]/', $value)) {
            $errors[] = 'one lowercase letter';
        }

        // Numbers requirement
        if ($this->requireNumbers && ! preg_match('/[0-9]/', $value)) {
            $errors[] = 'one number';
        }

        // Special characters requirement
        if ($this->requireSpecialChars) {
            $escapedChars = preg_quote($this->specialChars, '/');
            if (! preg_match("/[{$escapedChars}]/", $value)) {
                $errors[] = 'one special character';
            }
        }

        if (! empty($errors)) {
            $message = 'The password must contain ' . $this->formatErrors($errors) . '.';
            $fail($message);
        }
    }

    protected function formatErrors(array $errors): string
    {
        if (count($errors) === 1) {
            return $errors[0];
        }

        $last = array_pop($errors);
        return implode(', ', $errors) . ' and ' . $last;
    }

    /**
     * Get password requirements for display.
     */
    public static function requirements(): array
    {
        return [
            [
                'key' => 'min_length',
                'label' => 'Minimum 8 characters',
                'pattern' => '.{8,}',
            ],
            [
                'key' => 'uppercase',
                'label' => 'One uppercase letter',
                'pattern' => '[A-Z]',
            ],
            [
                'key' => 'lowercase',
                'label' => 'One lowercase letter',
                'pattern' => '[a-z]',
            ],
            [
                'key' => 'number',
                'label' => 'One number',
                'pattern' => '[0-9]',
            ],
            [
                'key' => 'special',
                'label' => 'One special character',
                'pattern' => '[!@#$%^&*()_+\\-=\\[\\]{}|;:,.<>?]',
            ],
        ];
    }
}

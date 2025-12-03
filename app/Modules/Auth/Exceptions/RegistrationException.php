<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class RegistrationException extends Exception
{
    public static function emailAlreadyRegistered(string $email): self
    {
        return new self("The email address {$email} is already registered. Please sign in instead.");
    }

    public static function invalidActivationCode(): self
    {
        return new self('The activation code is invalid. Please check your email and try again.');
    }

    public static function activationCodeExpired(): self
    {
        return new self('The activation code has expired. Please request a new code.');
    }

    public static function registrationNotFound(): self
    {
        return new self('Registration not found or has already been completed.');
    }

    public static function emailNotVerified(): self
    {
        return new self('Please verify your email address first.');
    }

    public static function invalidStep(string $message): self
    {
        return new self($message);
    }

    public static function registrationExpired(): self
    {
        return new self('This registration has expired. Please start over.');
    }
}

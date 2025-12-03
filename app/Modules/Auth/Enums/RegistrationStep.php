<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum RegistrationStep: string
{
    case EMAIL_SUBMITTED = 'email_submitted';
    case EMAIL_VERIFIED = 'email_verified';
    case PROFILE_COMPLETED = 'profile_completed';
    case COMPANY_COMPLETED = 'company_completed';
    case INVITATIONS_SENT = 'invitations_sent';
    case COMPLETED = 'completed';

    public function stepNumber(): int
    {
        return match ($this) {
            self::EMAIL_SUBMITTED => 1,
            self::EMAIL_VERIFIED => 1,
            self::PROFILE_COMPLETED => 2,
            self::COMPANY_COMPLETED => 3,
            self::INVITATIONS_SENT => 4,
            self::COMPLETED => 5,
        };
    }

    public function isComplete(): bool
    {
        return $this === self::COMPLETED;
    }

    public function next(): ?self
    {
        return match ($this) {
            self::EMAIL_SUBMITTED => self::EMAIL_VERIFIED,
            self::EMAIL_VERIFIED => self::PROFILE_COMPLETED,
            self::PROFILE_COMPLETED => self::COMPANY_COMPLETED,
            self::COMPANY_COMPLETED => self::INVITATIONS_SENT,
            self::INVITATIONS_SENT => self::COMPLETED,
            self::COMPLETED => null,
        };
    }
}

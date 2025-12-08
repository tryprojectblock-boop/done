<?php

declare(strict_types=1);

namespace App\Modules\Admin\Enums;

enum AdminRole: string
{
    case ADMINISTRATOR = 'administrator';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRATOR => 'Administrator',
            self::MEMBER => 'Admin Member',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMINISTRATOR => 'error',
            self::MEMBER => 'info',
        };
    }

    public function canManageAdmins(): bool
    {
        return $this === self::ADMINISTRATOR;
    }

    public function canManageSettings(): bool
    {
        return $this === self::ADMINISTRATOR;
    }
}

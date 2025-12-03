<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Enums;

enum WorkspaceStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ARCHIVED => 'warning',
            self::SUSPENDED => 'error',
        };
    }

    public function isAccessible(): bool
    {
        return $this === self::ACTIVE;
    }
}

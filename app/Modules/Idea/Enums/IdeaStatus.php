<?php

declare(strict_types=1);

namespace App\Modules\Idea\Enums;

enum IdeaStatus: string
{
    case NEW = 'new';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IMPLEMENTED = 'implemented';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::IMPLEMENTED => 'Implemented',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => '#3b82f6',
            self::UNDER_REVIEW => '#f59e0b',
            self::APPROVED => '#22c55e',
            self::REJECTED => '#ef4444',
            self::IMPLEMENTED => '#8b5cf6',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'tabler--bulb',
            self::UNDER_REVIEW => 'tabler--eye',
            self::APPROVED => 'tabler--check',
            self::REJECTED => 'tabler--x',
            self::IMPLEMENTED => 'tabler--rocket',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }
}

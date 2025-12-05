<?php

declare(strict_types=1);

namespace App\Modules\Idea\Enums;

enum IdeaPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => '#22c55e',
            self::MEDIUM => '#f59e0b',
            self::HIGH => '#ef4444',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOW => 'tabler--arrow-down',
            self::MEDIUM => 'tabler--minus',
            self::HIGH => 'tabler--arrow-up',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $priority) {
            $options[$priority->value] = $priority->label();
        }
        return $options;
    }
}

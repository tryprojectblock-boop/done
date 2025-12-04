<?php

declare(strict_types=1);

namespace App\Modules\Task\Enums;

enum TaskPriority: string
{
    case LOWEST = 'lowest';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case HIGHEST = 'highest';

    public function label(): string
    {
        return match ($this) {
            self::LOWEST => 'Lowest',
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::HIGHEST => 'Highest',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOWEST => 'tabler--chevrons-down',
            self::LOW => 'tabler--chevron-down',
            self::MEDIUM => 'tabler--minus',
            self::HIGH => 'tabler--chevron-up',
            self::HIGHEST => 'tabler--chevrons-up',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOWEST => '#64748b',  // slate
            self::LOW => '#3b82f6',     // blue/info
            self::MEDIUM => '#f59e0b',  // amber/warning
            self::HIGH => '#f97316',    // orange
            self::HIGHEST => '#ef4444', // red/error
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::LOWEST => 1,
            self::LOW => 2,
            self::MEDIUM => 3,
            self::HIGH => 4,
            self::HIGHEST => 5,
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $priority) => [
            'value' => $priority->value,
            'label' => $priority->label(),
            'icon' => $priority->icon(),
            'color' => $priority->color(),
        ], self::cases());
    }

    public static function default(): self
    {
        return self::MEDIUM;
    }
}

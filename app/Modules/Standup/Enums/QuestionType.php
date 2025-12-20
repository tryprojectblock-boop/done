<?php

declare(strict_types=1);

namespace App\Modules\Standup\Enums;

enum QuestionType: string
{
    case YESTERDAY = 'yesterday';
    case TODAY = 'today';
    case BLOCKERS = 'blockers';
    case OPTIONAL = 'optional';
    case MOOD = 'mood';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::YESTERDAY => 'Yesterday',
            self::TODAY => 'Today',
            self::BLOCKERS => 'Blockers',
            self::OPTIONAL => 'Optional',
            self::MOOD => 'Mood',
            self::CUSTOM => 'Custom',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::YESTERDAY => 'icon-[tabler--clock-hour-4]',
            self::TODAY => 'icon-[tabler--calendar-today]',
            self::BLOCKERS => 'icon-[tabler--alert-triangle]',
            self::OPTIONAL => 'icon-[tabler--message-circle]',
            self::MOOD => 'icon-[tabler--mood-smile]',
            self::CUSTOM => 'icon-[tabler--edit]',
        };
    }

    public function isDefault(): bool
    {
        return in_array($this, [
            self::YESTERDAY,
            self::TODAY,
            self::BLOCKERS,
            self::OPTIONAL,
            self::MOOD,
        ]);
    }

    public static function defaultTypes(): array
    {
        return [
            self::YESTERDAY,
            self::TODAY,
            self::BLOCKERS,
            self::OPTIONAL,
            self::MOOD,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Enums;

enum DiscussionType: string
{
    case GENERAL = 'general';
    case ANNOUNCEMENT = 'announcement';
    case QUESTION = 'question';
    case FEEDBACK = 'feedback';
    case BRAINSTORM = 'brainstorm';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::ANNOUNCEMENT => 'Announcement',
            self::QUESTION => 'Question',
            self::FEEDBACK => 'Feedback',
            self::BRAINSTORM => 'Brainstorm',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GENERAL => '#6b7280',
            self::ANNOUNCEMENT => '#f59e0b',
            self::QUESTION => '#3b82f6',
            self::FEEDBACK => '#8b5cf6',
            self::BRAINSTORM => '#10b981',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GENERAL => 'tabler--message-circle',
            self::ANNOUNCEMENT => 'tabler--speakerphone',
            self::QUESTION => 'tabler--help',
            self::FEEDBACK => 'tabler--message-report',
            self::BRAINSTORM => 'tabler--bulb',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }
        return $options;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Task\Enums;

enum TaskType: string
{
    case TASK = 'task';
    case BUG = 'bug';
    case FEATURE = 'feature';
    case IMPROVEMENT = 'improvement';
    case STORY = 'story';
    case SUBTASK = 'subtask';
    case INVESTIGATION = 'investigation';
    case QA = 'qa';
    case DESIGN = 'design';
    case DOCUMENTATION = 'documentation';
    case REVIEW = 'review';
    case RESEARCH = 'research';
    case MEETING = 'meeting';
    case DEPLOYMENT = 'deployment';
    case SUPPORT = 'support';
    case IDEA = 'idea';

    public function label(): string
    {
        return match ($this) {
            self::TASK => 'Task',
            self::BUG => 'Bug',
            self::FEATURE => 'Feature',
            self::IMPROVEMENT => 'Improvement',
            self::STORY => 'Story',
            self::SUBTASK => 'Subtask',
            self::INVESTIGATION => 'Investigation',
            self::QA => 'QA',
            self::DESIGN => 'Design',
            self::DOCUMENTATION => 'Documentation',
            self::REVIEW => 'Review',
            self::RESEARCH => 'Research',
            self::MEETING => 'Meeting / Discussion',
            self::DEPLOYMENT => 'Deployment / Release',
            self::SUPPORT => 'Support Request',
            self::IDEA => 'Idea / Proposal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TASK => 'tabler--checkbox',
            self::BUG => 'tabler--bug',
            self::FEATURE => 'tabler--sparkles',
            self::IMPROVEMENT => 'tabler--trending-up',
            self::STORY => 'tabler--book',
            self::SUBTASK => 'tabler--subtask',
            self::INVESTIGATION => 'tabler--search',
            self::QA => 'tabler--test-pipe',
            self::DESIGN => 'tabler--palette',
            self::DOCUMENTATION => 'tabler--file-text',
            self::REVIEW => 'tabler--eye-check',
            self::RESEARCH => 'tabler--flask',
            self::MEETING => 'tabler--users',
            self::DEPLOYMENT => 'tabler--rocket',
            self::SUPPORT => 'tabler--headset',
            self::IDEA => 'tabler--bulb',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TASK => 'primary',
            self::BUG => 'error',
            self::FEATURE => 'success',
            self::IMPROVEMENT => 'info',
            self::STORY => 'secondary',
            self::SUBTASK => 'neutral',
            self::INVESTIGATION => 'warning',
            self::QA => 'accent',
            self::DESIGN => 'pink',
            self::DOCUMENTATION => 'slate',
            self::REVIEW => 'indigo',
            self::RESEARCH => 'purple',
            self::MEETING => 'cyan',
            self::DEPLOYMENT => 'orange',
            self::SUPPORT => 'teal',
            self::IDEA => 'amber',
        };
    }

    public function isSubtask(): bool
    {
        return $this === self::SUBTASK;
    }

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'icon' => $type->icon(),
            'color' => $type->color(),
        ], self::cases());
    }

    public static function mainTypes(): array
    {
        return array_filter(
            self::cases(),
            fn (self $type) => $type !== self::SUBTASK
        );
    }
}

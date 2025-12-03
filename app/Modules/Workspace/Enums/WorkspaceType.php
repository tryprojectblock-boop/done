<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Enums;

enum WorkspaceType: string
{
    case CLASSIC = 'classic';
    case PRODUCT = 'product';

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic Workspace',
            self::PRODUCT => 'Product Workspace',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CLASSIC => 'For small businesses, agencies, and teams who need lightweight project management without agile complexity.',
            self::PRODUCT => 'Designed for product teams working with Roadmaps, Backlogs, Sprints, Epics, and full agile lifecycle.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CLASSIC => 'tabler--briefcase',
            self::PRODUCT => 'tabler--rocket',
        };
    }

    public function defaultModules(): array
    {
        return match ($this) {
            self::CLASSIC => [
                'message_board',
                'todos',
                'docs_files',
                'chat',
                'schedule',
                'check_ins',
                'activity_feed',
            ],
            self::PRODUCT => [
                'backlog',
                'epics',
                'sprints',
                'roadmap',
                'user_stories',
                'changelog',
                'research',
            ],
        };
    }

    public function availableModules(): array
    {
        return match ($this) {
            self::CLASSIC => [
                'message_board' => 'Message Board',
                'todos' => 'To-Dos',
                'docs_files' => 'Docs & Files',
                'chat' => 'Campfire / Chat',
                'schedule' => 'Schedule / Calendar',
                'check_ins' => 'Automatic Check-ins',
                'activity_feed' => 'Activity Feed',
            ],
            self::PRODUCT => [
                'backlog' => 'Backlog',
                'epics' => 'Epics',
                'sprints' => 'Sprints / Iterations',
                'roadmap' => 'Roadmap View',
                'user_stories' => 'User Stories',
                'changelog' => 'Changelog',
                'research' => 'Research / Docs',
                'feedback' => 'Feedback',
                'dev_sync' => 'Dev Sync',
            ],
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'description' => $type->description(),
            'icon' => $type->icon(),
        ], self::cases());
    }
}

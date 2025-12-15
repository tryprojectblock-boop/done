<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Enums;

enum WorkspaceType: string
{
    case CLASSIC = 'classic';
    case PRODUCT = 'product';
    case INBOX = 'inbox';

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic Workspace',
            self::PRODUCT => 'Product Workspace',
            self::INBOX => 'Inbox Workspace',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CLASSIC => 'For small businesses, agencies, and teams who need lightweight project management without agile complexity.',
            self::PRODUCT => 'Designed for product teams working with Roadmaps, Backlogs, Sprints, Epics, and full agile lifecycle.',
            self::INBOX => 'For support teams, help desks, and request management with triage and assignment workflows.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CLASSIC => 'tabler--briefcase',
            self::PRODUCT => 'tabler--rocket',
            self::INBOX => 'tabler--inbox',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::CLASSIC => 'primary',
            self::PRODUCT => 'secondary',
            self::INBOX => 'info',
        };
    }

    public function themeColor(): string
    {
        return match ($this) {
            self::CLASSIC => '#3b82f6', // blue
            self::PRODUCT => '#8b5cf6', // purple
            self::INBOX => '#06b6d4',   // cyan
        };
    }

    public function workflowType(): string
    {
        return match ($this) {
            self::CLASSIC => 'classic',
            self::PRODUCT => 'product',
            self::INBOX => 'inbox',
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
            self::INBOX => [
                'inbox',
                'triage',
                'assignments',
                'templates',
                'auto_replies',
                'reports',
                'sla_tracking',
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
            self::INBOX => [
                'inbox' => 'Inbox Queue',
                'triage' => 'Triage View',
                'assignments' => 'Assignments',
                'templates' => 'Response Templates',
                'auto_replies' => 'Auto Replies',
                'reports' => 'Reports & Analytics',
                'sla_tracking' => 'SLA Tracking',
                'canned_responses' => 'Canned Responses',
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

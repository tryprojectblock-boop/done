<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Enums;

enum WorkspaceRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case GUEST = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Administrator',
            self::MEMBER => 'Member',
            self::GUEST => 'Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OWNER => 'Full control over workspace settings, billing, and can delete the workspace.',
            self::ADMIN => 'Can manage members, settings, and all content within the workspace.',
            self::MEMBER => 'Can create and manage their own content, collaborate with others.',
            self::GUEST => 'Limited access to specific projects or items they are invited to.',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::OWNER => [
                'workspace.delete',
                'workspace.settings',
                'workspace.billing',
                'members.manage',
                'members.remove',
                'roles.assign',
                'content.create',
                'content.edit',
                'content.delete',
                'content.view',
            ],
            self::ADMIN => [
                'workspace.settings',
                'members.manage',
                'members.remove',
                'roles.assign',
                'content.create',
                'content.edit',
                'content.delete',
                'content.view',
            ],
            self::MEMBER => [
                'content.create',
                'content.edit.own',
                'content.delete.own',
                'content.view',
            ],
            self::GUEST => [
                'content.view.assigned',
            ],
        };
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    public function canManageSettings(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    public function canDeleteWorkspace(): bool
    {
        return $this === self::OWNER;
    }

    public function level(): int
    {
        return match ($this) {
            self::OWNER => 100,
            self::ADMIN => 75,
            self::MEMBER => 50,
            self::GUEST => 25,
        };
    }

    public function isHigherThan(self $role): bool
    {
        return $this->level() > $role->level();
    }

    public function isAtLeast(self $role): bool
    {
        return $this->level() >= $role->level();
    }
}

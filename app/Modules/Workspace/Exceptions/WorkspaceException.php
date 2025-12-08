<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Exceptions;

use Exception;

final class WorkspaceException extends Exception
{
    public static function notFound(string $identifier): self
    {
        return new self("Workspace not found: {$identifier}");
    }

    public static function accessDenied(): self
    {
        return new self('You do not have permission to access this workspace.');
    }

    public static function alreadyMember(string $email): self
    {
        return new self("{$email} is already a member of this workspace.");
    }

    public static function invitationAlreadySent(string $email): self
    {
        return new self("An invitation has already been sent to {$email}.");
    }

    public static function invitationExpired(): self
    {
        return new self('This invitation has expired. Please request a new invitation.');
    }

    public static function invitationNotFound(): self
    {
        return new self('Invitation not found or has already been used.');
    }

    public static function cannotRemoveSelf(): self
    {
        return new self('You cannot remove yourself from the workspace. Transfer ownership first.');
    }

    public static function cannotRemoveOwner(): self
    {
        return new self('The workspace owner cannot be removed. Transfer ownership first.');
    }

    public static function insufficientPermissions(string $action): self
    {
        return new self("You do not have permission to {$action}.");
    }

    public static function moduleNotEnabled(string $module): self
    {
        return new self("The {$module} module is not enabled for this workspace.");
    }

    public static function workspaceArchived(): self
    {
        return new self('This workspace has been archived and cannot be modified.');
    }

    public static function workspaceSuspended(): self
    {
        return new self('This workspace has been suspended. Please contact support.');
    }

    public static function workspaceLimitReached(): self
    {
        return new self('You have reached the maximum number of workspaces allowed by your plan. Please upgrade to create more workspaces.');
    }

    public static function teamMemberLimitReached(): self
    {
        return new self('You have reached the maximum number of team members allowed by your plan. Please upgrade to invite more members.');
    }

    public static function storageLimitReached(): self
    {
        return new self('You have reached the storage limit for your plan. Please upgrade to upload more files.');
    }
}

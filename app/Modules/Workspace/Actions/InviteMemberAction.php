<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Actions;

use App\Models\User;
use App\Modules\Core\Support\BaseAction;
use App\Modules\Workspace\DTOs\InviteMemberDTO;
use App\Modules\Workspace\Events\MemberInvited;
use App\Modules\Workspace\Exceptions\WorkspaceException;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceInvitation;

final class InviteMemberAction extends BaseAction
{
    protected function handle(mixed ...$args): WorkspaceInvitation
    {
        /** @var InviteMemberDTO $dto */
        $dto = $args[0];

        $workspace = Workspace::findOrFail($dto->workspaceId);

        // Check if user already exists and is a member
        $existingUser = User::where('email', $dto->email)->first();
        if ($existingUser && $workspace->hasMember($existingUser)) {
            throw WorkspaceException::alreadyMember($dto->email);
        }

        // Check for pending invitation
        $existingInvitation = WorkspaceInvitation::where('workspace_id', $dto->workspaceId)
            ->forEmail($dto->email)
            ->pending()
            ->first();

        if ($existingInvitation) {
            throw WorkspaceException::invitationAlreadySent($dto->email);
        }

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $dto->workspaceId,
            'email' => $dto->email,
            'role' => $dto->role,
            'invited_by' => $dto->invitedBy,
        ]);

        event(new MemberInvited($invitation, $dto->message));

        return $invitation;
    }
}

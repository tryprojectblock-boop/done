<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Services;

use App\Models\User;
use App\Modules\Core\Contracts\FileUploadInterface;
use App\Modules\Workspace\Actions\CreateWorkspaceAction;
use App\Modules\Workspace\Actions\InviteMemberAction;
use App\Modules\Workspace\Contracts\WorkspaceServiceInterface;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\DTOs\InviteMemberDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Enums\WorkspaceStatus;
use App\Modules\Workspace\Events\WorkspaceCreated;
use App\Modules\Workspace\Exceptions\WorkspaceException;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceInvitation;
use App\Services\PlanLimitService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class WorkspaceService implements WorkspaceServiceInterface
{
    public function __construct(
        private readonly FileUploadInterface $fileUpload,
        private readonly CreateWorkspaceAction $createWorkspaceAction,
        private readonly InviteMemberAction $inviteMemberAction,
        private readonly PlanLimitService $planLimitService,
    ) {}

    public function create(CreateWorkspaceDTO $dto): Workspace
    {
        // Check workspace limit before creating
        $owner = User::find($dto->ownerId);

        if ($owner && $owner->company) {
            if (!$this->planLimitService->canCreateWorkspace($owner->company)) {
                throw WorkspaceException::workspaceLimitReached();
            }
        }

        return $this->createWorkspaceAction->execute($dto);
    }

    public function update(Workspace $workspace, array $data): Workspace
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        $workspace->update($data);

        return $workspace->fresh();
    }

    public function delete(Workspace $workspace): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        DB::transaction(function () use ($workspace) {
            // Remove all members
            $workspace->members()->detach();

            // Delete pending invitations
            $workspace->invitations()->delete();

            // Delete the workspace (soft delete)
            $workspace->delete();
        });
    }

    public function archive(Workspace $workspace): void
    {
        $workspace->update(['status' => WorkspaceStatus::ARCHIVED]);
    }

    public function restore(Workspace $workspace): void
    {
        $workspace->update(['status' => WorkspaceStatus::ACTIVE]);
    }

    public function findByUuid(string $uuid): ?Workspace
    {
        return Workspace::findByUuid($uuid);
    }

    public function getForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Workspace::where('owner_id', $user->id)
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    public function getAllForUser(User $user): Collection
    {
        return Workspace::where('owner_id', $user->id)
            ->active()
            ->latest()
            ->get();
    }

    public function invite(InviteMemberDTO $dto): WorkspaceInvitation
    {
        return $this->inviteMemberAction->execute($dto);
    }

    public function acceptInvitation(string $token, User $user): Workspace
    {
        $invitation = WorkspaceInvitation::findByToken($token);

        if (! $invitation) {
            throw WorkspaceException::invitationNotFound();
        }

        if ($invitation->isExpired()) {
            throw WorkspaceException::invitationExpired();
        }

        if ($invitation->isAccepted()) {
            throw WorkspaceException::invitationNotFound();
        }

        // Verify email matches
        if (strtolower($invitation->email) !== strtolower($user->email)) {
            throw WorkspaceException::invitationNotFound();
        }

        $invitation->accept($user);

        return $invitation->workspace;
    }

    public function removeMember(Workspace $workspace, User $member, User $removedBy): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        if ($workspace->isOwner($member)) {
            throw WorkspaceException::cannotRemoveOwner();
        }

        if ($member->id === $removedBy->id) {
            throw WorkspaceException::cannotRemoveSelf();
        }

        // Check if the remover has permission
        $removerRole = $workspace->getMemberRole($removedBy);
        $memberRole = $workspace->getMemberRole($member);

        if (! $removerRole || ! $removerRole->canManageMembers()) {
            throw WorkspaceException::insufficientPermissions('remove members');
        }

        if ($memberRole && ! $removerRole->isHigherThan($memberRole)) {
            throw WorkspaceException::insufficientPermissions('remove this member');
        }

        $workspace->removeMember($member);
    }

    public function updateMemberRole(Workspace $workspace, User $member, WorkspaceRole $role, User $updatedBy): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        // Cannot change owner role
        if ($workspace->isOwner($member)) {
            throw WorkspaceException::insufficientPermissions('change owner role');
        }

        // Cannot set someone as owner this way
        if ($role === WorkspaceRole::OWNER) {
            throw WorkspaceException::insufficientPermissions('assign owner role directly');
        }

        $updaterRole = $workspace->getMemberRole($updatedBy);

        if (! $updaterRole || ! $updaterRole->canManageMembers()) {
            throw WorkspaceException::insufficientPermissions('manage member roles');
        }

        $workspace->updateMemberRole($member, $role);
    }

    public function transferOwnership(Workspace $workspace, User $newOwner, User $currentOwner): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        if (! $workspace->isOwner($currentOwner)) {
            throw WorkspaceException::insufficientPermissions('transfer ownership');
        }

        if (! $workspace->hasMember($newOwner)) {
            throw WorkspaceException::insufficientPermissions('transfer to non-member');
        }

        DB::transaction(function () use ($workspace, $newOwner, $currentOwner) {
            // Update workspace owner
            $workspace->update(['owner_id' => $newOwner->id]);

            // Update roles
            $workspace->updateMemberRole($newOwner, WorkspaceRole::OWNER);
            $workspace->updateMemberRole($currentOwner, WorkspaceRole::ADMIN);
        });
    }

    public function updateLogo(Workspace $workspace, $logo): Workspace
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        // Delete old logo if exists
        if ($workspace->logo_path) {
            $this->fileUpload->delete($workspace->logo_path);
        }

        if ($logo instanceof UploadedFile) {
            $result = $this->fileUpload->upload($logo, 'workspaces/logos', [
                'visibility' => 'public',
                'tenant_id' => $workspace->tenant_id,
            ]);

            if ($result->isSuccess()) {
                $workspace->update(['logo_path' => $result->path]);
            }
        }

        return $workspace->fresh();
    }

    public function enableModules(Workspace $workspace, array $modules): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        $availableModules = array_keys($workspace->type->availableModules());
        $validModules = array_intersect($modules, $availableModules);

        foreach ($validModules as $module) {
            $workspace->enableModule($module);
        }
    }

    public function disableModules(Workspace $workspace, array $modules): void
    {
        $this->ensureWorkspaceIsAccessible($workspace);

        foreach ($modules as $module) {
            $workspace->disableModule($module);
        }
    }

    private function ensureWorkspaceIsAccessible(Workspace $workspace): void
    {
        if ($workspace->status === WorkspaceStatus::ARCHIVED) {
            throw WorkspaceException::workspaceArchived();
        }

        if ($workspace->status === WorkspaceStatus::SUSPENDED) {
            throw WorkspaceException::workspaceSuspended();
        }
    }
}

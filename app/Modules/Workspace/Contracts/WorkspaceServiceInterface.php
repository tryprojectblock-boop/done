<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Contracts;

use App\Models\User;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\DTOs\InviteMemberDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceInvitation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WorkspaceServiceInterface
{
    public function create(CreateWorkspaceDTO $dto): Workspace;

    public function update(Workspace $workspace, array $data): Workspace;

    public function delete(Workspace $workspace): void;

    public function archive(Workspace $workspace): void;

    public function restore(Workspace $workspace): void;

    public function findByUuid(string $uuid): ?Workspace;

    public function getForUser(User $user, int $perPage = 15): LengthAwarePaginator;

    public function getOtherCompanyWorkspaces(User $user): Collection;

    public function getAllForUser(User $user): Collection;

    public function invite(InviteMemberDTO $dto): WorkspaceInvitation;

    public function acceptInvitation(string $token, User $user): Workspace;

    public function removeMember(Workspace $workspace, User $member, User $removedBy): void;

    public function updateMemberRole(Workspace $workspace, User $member, WorkspaceRole $role, User $updatedBy): void;

    public function transferOwnership(Workspace $workspace, User $newOwner, User $currentOwner): void;

    public function updateLogo(Workspace $workspace, $logo): Workspace;

    public function enableModules(Workspace $workspace, array $modules): void;

    public function disableModules(Workspace $workspace, array $modules): void;
}

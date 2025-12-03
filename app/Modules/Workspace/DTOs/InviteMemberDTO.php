<?php

declare(strict_types=1);

namespace App\Modules\Workspace\DTOs;

use App\Modules\Core\Support\DataTransferObject;
use App\Modules\Workspace\Enums\WorkspaceRole;

final class InviteMemberDTO extends DataTransferObject
{
    public function __construct(
        public readonly int $workspaceId,
        public readonly string $email,
        public readonly WorkspaceRole $role,
        public readonly int $invitedBy,
        public readonly ?string $message = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            workspaceId: $data['workspace_id'],
            email: strtolower(trim($data['email'])),
            role: $data['role'] instanceof WorkspaceRole
                ? $data['role']
                : WorkspaceRole::from($data['role'] ?? 'member'),
            invitedBy: $data['invited_by'],
            message: $data['message'] ?? null,
        );
    }
}

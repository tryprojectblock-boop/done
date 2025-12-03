<?php

declare(strict_types=1);

namespace App\Modules\Workspace\DTOs;

use App\Modules\Core\Support\DataTransferObject;
use App\Modules\Workspace\Enums\WorkspaceType;

final class CreateWorkspaceDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly WorkspaceType $type,
        public readonly int $ownerId,
        public readonly ?string $description = null,
        public readonly ?int $workflowId = null,
        public readonly ?string $slug = null,
        public readonly ?string $color = null,
        public readonly array $enabledModules = [],
        public readonly array $settings = [],
        public readonly ?int $tenantId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            type: $data['type'] instanceof WorkspaceType
                ? $data['type']
                : WorkspaceType::from($data['type']),
            ownerId: $data['owner_id'],
            description: $data['description'] ?? null,
            workflowId: $data['workflow_id'] ?? null,
            slug: $data['slug'] ?? null,
            color: $data['color'] ?? null,
            enabledModules: $data['enabled_modules'] ?? [],
            settings: $data['settings'] ?? [],
            tenantId: $data['tenant_id'] ?? null,
        );
    }
}

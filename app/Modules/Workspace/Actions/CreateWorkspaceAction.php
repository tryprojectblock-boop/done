<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Actions;

use App\Modules\Core\Support\BaseAction;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Events\WorkspaceCreated;
use App\Modules\Workspace\Models\Workspace;

final class CreateWorkspaceAction extends BaseAction
{
    protected function handle(mixed ...$args): Workspace
    {
        /** @var CreateWorkspaceDTO $dto */
        $dto = $args[0];

        $workspace = Workspace::create([
            'name' => $dto->name,
            'slug' => $dto->slug ?? str($dto->name)->slug(),
            'description' => $dto->description,
            'type' => $dto->type,
            'owner_id' => $dto->ownerId,
            'tenant_id' => $dto->tenantId ?? current_tenant_id(),
            'color' => $dto->color,
            'enabled_modules' => ! empty($dto->enabledModules)
                ? $dto->enabledModules
                : $dto->type->defaultModules(),
            'settings' => $dto->settings,
        ]);

        // Add owner as a member with owner role
        $workspace->addMember(
            $workspace->owner,
            WorkspaceRole::OWNER
        );

        event(new WorkspaceCreated($workspace));

        return $workspace;
    }
}

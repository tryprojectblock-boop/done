<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Listeners;

use App\Modules\Workspace\Events\WorkspaceCreated;
use Database\Seeders\WorkflowTemplateSeeder;

final class CreateDefaultWorkflows
{
    /**
     * Handle the WorkspaceCreated event.
     */
    public function handle(WorkspaceCreated $event): void
    {
        WorkflowTemplateSeeder::createForWorkspace(
            $event->workspace,
            $event->workspace->owner_id
        );
    }
}

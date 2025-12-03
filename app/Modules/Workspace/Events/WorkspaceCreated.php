<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Events;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class WorkspaceCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Workspace $workspace,
    ) {}
}

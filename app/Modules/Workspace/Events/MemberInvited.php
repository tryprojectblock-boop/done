<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Events;

use App\Modules\Workspace\Models\WorkspaceInvitation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MemberInvited
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly WorkspaceInvitation $invitation,
        public readonly ?string $message = null,
    ) {}
}

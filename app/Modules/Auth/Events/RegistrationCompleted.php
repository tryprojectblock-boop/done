<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RegistrationCompleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Company $company,
        public readonly Workspace $workspace,
    ) {}
}

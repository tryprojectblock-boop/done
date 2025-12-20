<?php

declare(strict_types=1);

namespace App\Modules\Admin\Listeners;

use App\Models\User;
use App\Modules\Admin\Services\FunnelTagService;
use App\Modules\Auth\Events\RegistrationCompleted;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Workspace\Events\MemberInvited;
use Illuminate\Contracts\Queue\ShouldQueue;

class FunnelTagListener implements ShouldQueue
{
    public function __construct(
        protected FunnelTagService $tagService
    ) {}

    /**
     * Handle user registered event.
     */
    public function handleUserRegistered(UserRegistered $event): void
    {
        $this->tagService->addTag($event->user, 'pb_signed_up');
    }

    /**
     * Handle registration completed event (includes company creation).
     */
    public function handleRegistrationCompleted(RegistrationCompleted $event): void
    {
        // User has created their first workspace (company)
        $this->tagService->addTag($event->user, 'pb_workspace_created');
    }

    /**
     * Handle member invited event.
     */
    public function handleMemberInvited(MemberInvited $event): void
    {
        // Tag the user who sent the invitation (not the invitee)
        if ($event->invitedBy) {
            $this->tagService->addTag($event->invitedBy, 'pb_team_invited');
        }
    }
}

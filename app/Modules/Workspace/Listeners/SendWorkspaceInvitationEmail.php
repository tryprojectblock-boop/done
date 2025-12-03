<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Listeners;

use App\Modules\Workspace\Events\MemberInvited;
use App\Modules\Workspace\Mail\WorkspaceInvitationMail;
use Illuminate\Support\Facades\Mail;

class SendWorkspaceInvitationEmail
{
    /**
     * Handle the event.
     */
    public function handle(MemberInvited $event): void
    {
        Mail::to($event->invitation->email)
            ->send(new WorkspaceInvitationMail(
                $event->invitation,
                $event->message,
            ));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Mail;

use App\Modules\Workspace\Models\WorkspaceInvitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WorkspaceInvitationMail extends Mailable
{
    public function __construct(
        public readonly WorkspaceInvitation $invitation,
        public readonly ?string $personalMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        $inviterName = $this->invitation->inviter?->name ?? 'Someone';
        $workspaceName = $this->invitation->workspace->name;

        return new Envelope(
            subject: "{$inviterName} invited you to join {$workspaceName} on " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'workspace::emails.invitation',
            with: [
                'invitation' => $this->invitation,
                'workspace' => $this->invitation->workspace,
                'inviter' => $this->invitation->inviter,
                'personalMessage' => $this->personalMessage,
                'acceptUrl' => url("/invitation/{$this->invitation->token}/accept"),
            ],
        );
    }
}

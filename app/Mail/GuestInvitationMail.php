<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $signupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $guest,
        public User $invitedBy,
        public string $invitationToken
    ) {
        $this->signupUrl = url('/guest/signup/' . $invitationToken);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = $this->invitedBy->company?->name ?? config('app.name');

        return new Envelope(
            subject: "You've been invited to join {$companyName} as a guest",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetMail extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $resetUrl = url('/reset-password/' . $this->token . '?email=' . urlencode($this->user->email));

        return new Content(
            markdown: 'auth::emails.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $resetUrl,
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\Mail;

use App\Modules\Auth\Models\PendingRegistration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ActivationCodeMail extends Mailable
{

    public function __construct(
        public readonly PendingRegistration $registration,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your activation code for ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auth::emails.activation-code',
            with: [
                'code' => $this->registration->activation_code,
                'expiresAt' => $this->registration->activation_code_expires_at,
                'email' => $this->registration->email,
            ],
        );
    }
}

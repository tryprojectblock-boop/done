<?php

declare(strict_types=1);

namespace App\Modules\Admin\Mail;

use App\Modules\Admin\Models\AdminUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminUser $adminUser,
        public string $code
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Admin Panel Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin::emails.verification-code',
        );
    }
}

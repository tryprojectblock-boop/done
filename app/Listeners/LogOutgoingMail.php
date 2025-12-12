<?php

namespace App\Listeners;

use App\Models\MailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Cache;

class LogOutgoingMail
{
    public function handle(MessageSending $event): void
    {
        $message = $event->message;

        // Create a hash to prevent duplicate logging
        $hash = md5($message->getSubject() . json_encode($message->getTo()) . substr($message->getHtmlBody() ?? '', 0, 500));
        $cacheKey = 'mail_log_' . $hash;

        // Skip if already logged in the last 5 seconds
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, 5);

        $to = [];
        foreach ($message->getTo() as $address) {
            $to[] = [
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }

        $cc = [];
        foreach ($message->getCc() as $address) {
            $cc[] = [
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }

        $bcc = [];
        foreach ($message->getBcc() as $address) {
            $bcc[] = [
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }

        $from = $message->getFrom();
        $fromAddress = null;
        $fromName = null;
        foreach ($from as $address) {
            $fromAddress = $address->getAddress();
            $fromName = $address->getName();
            break;
        }

        $htmlBody = $message->getHtmlBody();
        $textBody = $message->getTextBody();

        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            $attachments[] = [
                'filename' => $attachment->getFilename(),
                'content_type' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ];
        }

        MailLog::create([
            'mailable_class' => $event->data['__mailable'] ?? null,
            'subject' => $message->getSubject(),
            'to' => $to,
            'cc' => $cc ?: null,
            'bcc' => $bcc ?: null,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'html_body' => $htmlBody,
            'text_body' => $textBody,
            'attachments' => $attachments ?: null,
        ]);
    }
}

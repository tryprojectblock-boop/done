<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceInboxSetting;
use App\Models\InboundEmail;
use App\Services\InboxEmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    /**
     * Handle inbound email from Mailgun.
     */
    public function handleInbound(Request $request): JsonResponse
    {
        Log::info('Mailgun inbound webhook received', [
            'recipient' => $request->input('recipient'),
            'sender' => $request->input('sender'),
            'subject' => $request->input('subject'),
        ]);

        // Verify Mailgun signature (important for security)
        if (!$this->verifyMailgunSignature($request)) {
            Log::warning('Mailgun webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Extract email data
        $recipient = $request->input('recipient');
        $sender = $request->input('sender');
        $from = $request->input('from');
        $subject = $request->input('subject', '(No Subject)');
        $bodyPlain = $request->input('body-plain', '');
        $bodyHtml = $request->input('body-html', '');
        $strippedText = $request->input('stripped-text', '');
        $strippedHtml = $request->input('stripped-html', '');
        $messageId = $request->input('Message-Id');
        $inReplyTo = $request->input('In-Reply-To');
        $references = $request->input('References');
        $attachmentCount = (int) $request->input('attachment-count', 0);

        // Find workspace by inbound email
        $inboxSettings = WorkspaceInboxSetting::where('inbound_email', $recipient)->first();

        if (!$inboxSettings) {
            Log::warning('No workspace found for inbound email', ['recipient' => $recipient]);
            return response()->json(['error' => 'Unknown recipient'], 404);
        }

        $workspace = $inboxSettings->workspace;

        // Mark email as verified if this is the first successful inbound email
        if (!$inboxSettings->email_verified) {
            $inboxSettings->update([
                'email_verified' => true,
                'email_verified_at' => now(),
            ]);
            Log::info('Inbox email verified via webhook', [
                'workspace_id' => $workspace->id,
                'inbound_email' => $recipient,
            ]);
        }

        // Parse sender name and email
        $senderParsed = $this->parseEmailAddress($from ?: $sender);

        // Store the inbound email
        $inboundEmail = InboundEmail::create([
            'workspace_id' => $workspace->id,
            'message_id' => $messageId,
            'in_reply_to' => $inReplyTo,
            'references' => $references,
            'from_email' => $senderParsed['email'],
            'from_name' => $senderParsed['name'],
            'to_email' => $recipient,
            'subject' => $subject,
            'body_plain' => $bodyPlain,
            'body_html' => $bodyHtml,
            'stripped_text' => $strippedText,
            'stripped_html' => $strippedHtml,
            'attachment_count' => $attachmentCount,
            'raw_payload' => json_encode($request->all()),
            'status' => 'pending',
        ]);

        // Handle attachments if any
        if ($attachmentCount > 0) {
            $this->processAttachments($request, $inboundEmail);
        }

        // Process the email (create ticket or add reply)
        $this->processInboundEmail($inboundEmail, $workspace);

        Log::info('Mailgun inbound email processed successfully', [
            'inbound_email_id' => $inboundEmail->id,
            'workspace_id' => $workspace->id,
        ]);

        return response()->json(['success' => true, 'id' => $inboundEmail->id]);
    }

    /**
     * Verify Mailgun webhook signature.
     */
    protected function verifyMailgunSignature(Request $request): bool
    {
        // Skip verification in local/development environment
        if (app()->environment('local', 'development')) {
            return true;
        }

        $apiKey = config('services.mailgun.secret');

        if (empty($apiKey)) {
            return false;
        }

        $timestamp = $request->input('timestamp');
        $token = $request->input('token');
        $signature = $request->input('signature');

        if (empty($timestamp) || empty($token) || empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . $token, $apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse email address string to extract name and email.
     */
    protected function parseEmailAddress(string $address): array
    {
        $name = null;
        $email = $address;

        // Match "Name <email@example.com>" format
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $address, $matches)) {
            $name = trim($matches[1], '"\'');
            $email = $matches[2];
        }

        return [
            'name' => $name,
            'email' => $email,
        ];
    }

    /**
     * Process attachments from the email.
     */
    protected function processAttachments(Request $request, InboundEmail $inboundEmail): void
    {
        $attachments = [];

        for ($i = 1; $i <= $inboundEmail->attachment_count; $i++) {
            $file = $request->file("attachment-$i");
            if ($file) {
                // Store attachment info (actual file storage can be implemented later)
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        if (!empty($attachments)) {
            $inboundEmail->update([
                'attachments' => json_encode($attachments),
            ]);
        }
    }

    /**
     * Process the inbound email - create ticket or add reply.
     */
    protected function processInboundEmail(InboundEmail $inboundEmail, Workspace $workspace): void
    {
        // Check if this is a reply to an existing ticket
        $existingTicketId = $this->findExistingTicket($inboundEmail, $workspace);

        if ($existingTicketId) {
            // This is a reply - add comment to existing ticket
            $this->addReplyToTicket($inboundEmail, $existingTicketId);
            $inboundEmail->update([
                'status' => 'processed',
                'processed_at' => now(),
                'ticket_id' => $existingTicketId,
                'is_reply' => true,
            ]);
        } else {
            // This is a new ticket
            $ticketId = $this->createTicketFromEmail($inboundEmail, $workspace);
            $inboundEmail->update([
                'status' => 'processed',
                'processed_at' => now(),
                'ticket_id' => $ticketId,
                'is_reply' => false,
            ]);
        }
    }

    /**
     * Find existing ticket based on email headers or subject.
     */
    protected function findExistingTicket(InboundEmail $inboundEmail, Workspace $workspace): ?int
    {
        // Check In-Reply-To header first
        if ($inboundEmail->in_reply_to) {
            $originalEmail = InboundEmail::where('message_id', $inboundEmail->in_reply_to)
                ->where('workspace_id', $workspace->id)
                ->first();

            if ($originalEmail && $originalEmail->ticket_id) {
                return $originalEmail->ticket_id;
            }
        }

        // Check for ticket ID in subject (e.g., [Ticket #123] or Re: Ticket #123)
        if (preg_match('/(?:Ticket|#)\s*#?(\d+)/i', $inboundEmail->subject, $matches)) {
            $ticketId = (int) $matches[1];
            // Verify ticket exists in this workspace
            $task = \App\Modules\Task\Models\Task::where('id', $ticketId)
                ->where('workspace_id', $workspace->id)
                ->first();

            if ($task) {
                return $task->id;
            }
        }

        return null;
    }

    /**
     * Create a new ticket/task from the inbound email.
     */
    protected function createTicketFromEmail(InboundEmail $inboundEmail, Workspace $workspace): int
    {
        // Get or create customer user
        $customerData = $this->getOrCreateCustomer($inboundEmail->from_email, $inboundEmail->from_name);
        $customer = $customerData['user'];
        $needsPortalEmail = $customerData['needs_portal_email'];

        // Add customer as guest to workspace if not already
        if (!$workspace->guests()->where('user_id', $customer->id)->exists()) {
            $workspace->guests()->attach($customer->id, ['invited_by' => $workspace->owner_id]);
            Log::info('Added customer as guest to workspace', [
                'user_id' => $customer->id,
                'workspace_id' => $workspace->id,
            ]);
        }

        // Get default status for inbox workspace
        $defaultStatus = $workspace->workflow?->statuses()
            ->where('is_default', true)
            ->first();

        // Fallback to first active status if no default
        if (!$defaultStatus) {
            $defaultStatus = $workspace->workflow?->statuses()
                ->where('is_active', true)
                ->first();
        }

        // Create the task/ticket
        $task = \App\Modules\Task\Models\Task::create([
            'workspace_id' => $workspace->id,
            'company_id' => $workspace->company_id ?? $workspace->owner?->company_id,
            'title' => $inboundEmail->subject,
            'description' => $inboundEmail->stripped_html ?: $inboundEmail->body_html ?: $inboundEmail->stripped_text ?: $inboundEmail->body_plain,
            'status_id' => $defaultStatus?->id,
            'created_by' => $customer->id,
            'source' => 'email',
            'source_email' => $inboundEmail->from_email,
        ]);

        Log::info('Created ticket from inbound email', [
            'task_id' => $task->id,
            'inbound_email_id' => $inboundEmail->id,
        ]);

        // Send confirmation email to customer
        $emailService = app(InboxEmailService::class);
        $emailService->sendTicketOpenedEmail($task);

        // Send client portal access email if enabled and customer needs it
        if ($needsPortalEmail) {
            $emailService->sendPortalAccessEmail($task, $customer);
        }

        // Notify workspace operators
        $emailService->sendOperatorTicketOpenedEmail($task);

        return $task->id;
    }

    /**
     * Add a reply/comment to an existing ticket.
     */
    protected function addReplyToTicket(InboundEmail $inboundEmail, int $ticketId): void
    {
        $customerData = $this->getOrCreateCustomer($inboundEmail->from_email, $inboundEmail->from_name);
        $customer = $customerData['user'];

        // Add comment to the task
        \App\Modules\Task\Models\TaskComment::create([
            'task_id' => $ticketId,
            'user_id' => $customer->id,
            'content' => $inboundEmail->stripped_html ?: $inboundEmail->body_html ?: $inboundEmail->stripped_text ?: $inboundEmail->body_plain,
            'source' => 'email',
        ]);

        // Update task's last activity
        \App\Modules\Task\Models\Task::where('id', $ticketId)->update([
            'updated_at' => now(),
        ]);

        Log::info('Added reply to ticket from inbound email', [
            'task_id' => $ticketId,
            'inbound_email_id' => $inboundEmail->id,
        ]);
    }

    /**
     * Get or create a customer user from email.
     *
     * @return array{user: \App\Models\User, needs_portal_email: bool}
     */
    protected function getOrCreateCustomer(string $email, ?string $name): array
    {
        $user = \App\Models\User::where('email', $email)->first();
        $needsPortalEmail = false;

        if (!$user) {
            // Create new guest user
            $invitationToken = \Illuminate\Support\Str::random(64);

            $user = \App\Models\User::create([
                'email' => $email,
                'name' => $name ?: explode('@', $email)[0],
                'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                'role' => \App\Models\User::ROLE_GUEST,
                'is_guest' => true,
                'status' => \App\Models\User::STATUS_INVITED,
                'invitation_token' => $invitationToken,
                'invitation_expires_at' => now()->addDays(30),
                'email_verified_at' => now(), // Auto-verify since they contacted via email
            ]);

            $needsPortalEmail = true;

            Log::info('Created customer user from inbound email', [
                'user_id' => $user->id,
                'email' => $email,
            ]);
        } else {
            // Existing user - check if they need portal access email
            // If guest without invitation token (created before portal feature), generate one
            if (!$user->invitation_token && $user->status !== \App\Models\User::STATUS_ACTIVE) {
                $invitationToken = \Illuminate\Support\Str::random(64);
                $user->update([
                    'is_guest' => true,
                    'invitation_token' => $invitationToken,
                    'invitation_expires_at' => now()->addDays(30),
                ]);
                $needsPortalEmail = true;

                Log::info('Generated invitation token for existing user', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            }
        }

        return ['user' => $user, 'needs_portal_email' => $needsPortalEmail];
    }
}

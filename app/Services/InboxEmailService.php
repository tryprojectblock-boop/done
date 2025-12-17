<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceEmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InboxEmailService
{
    /**
     * Send email using workspace template.
     */
    public function sendTemplateEmail(
        string $templateType,
        Task $task,
        ?string $recipientEmail = null,
        ?string $recipientName = null,
        ?User $user = null
    ): bool {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Get the template from database
        $template = $workspace->emailTemplates()
            ->where('type', $templateType)
            ->where('is_active', true)
            ->first();

        // Fall back to default template if no custom template exists
        $subject = null;
        $body = null;

        if ($template) {
            $subject = $template->subject;
            $body = $template->body;
        } else {
            // Use default template
            $defaults = WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults[$templateType])) {
                $subject = $defaults[$templateType]['subject'];
                $body = $defaults[$templateType]['body'];
                Log::info('Using default email template', [
                    'template_type' => $templateType,
                    'workspace_id' => $workspace->id,
                ]);
            } else {
                Log::warning('Email template not found', [
                    'template_type' => $templateType,
                    'workspace_id' => $workspace->id,
                ]);
                return false;
            }
        }

        // Determine recipient
        $toEmail = $recipientEmail ?? $task->source_email ?? $task->creator?->email;
        $toName = $recipientName ?? $task->creator?->name;

        if (!$toEmail) {
            Log::warning('No recipient email for template email', [
                'template_type' => $templateType,
                'task_id' => $task->id,
            ]);
            return false;
        }

        // Render the template
        $renderedSubject = $this->renderTemplate($subject, $task, $workspace, $user);
        $renderedBody = $this->renderTemplate($body, $task, $workspace, $user);

        // Get the from email from inbox settings
        $fromEmail = $workspace->inboxSettings?->from_email ?? config('mail.from.address');
        $fromName = $workspace->name;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $renderedSubject, $renderedBody, $fromEmail, $fromName) {
                $message->to($toEmail, $toName)
                    ->from($fromEmail, $fromName)
                    ->subject($renderedSubject)
                    ->html(nl2br(e($renderedBody)));
            });

            Log::info('Template email sent successfully', [
                'template_type' => $templateType,
                'task_id' => $task->id,
                'to' => $toEmail,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send template email', [
                'template_type' => $templateType,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send "New Ticket Opened" email to customer.
     */
    public function sendTicketOpenedEmail(Task $task): bool
    {
        return $this->sendTemplateEmail('user_ticket_opened', $task);
    }

    /**
     * Send "Ticket Closed" email to customer.
     */
    public function sendTicketClosedEmail(Task $task): bool
    {
        return $this->sendTemplateEmail('user_ticket_closed', $task);
    }

    /**
     * Send "New Ticket Reply" email to customer.
     */
    public function sendTicketReplyEmail(Task $task): bool
    {
        return $this->sendTemplateEmail('user_ticket_reply', $task);
    }

    /**
     * Send "Client Portal Access" email to customer.
     */
    public function sendPortalAccessEmail(Task $task, User $user): bool
    {
        $workspace = $task->workspace;

        // Only send if client portal is enabled
        if (!$workspace?->inboxSettings?->client_portal_enabled) {
            Log::info('Client portal not enabled, skipping portal access email', [
                'workspace_id' => $workspace?->id,
                'task_id' => $task->id,
            ]);
            return false;
        }

        Log::info('Sending portal access email', [
            'workspace_id' => $workspace->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'has_invitation_token' => !empty($user->invitation_token),
        ]);

        return $this->sendTemplateEmail(
            'user_portal_access',
            $task,
            $user->email,
            $user->name,
            $user
        );
    }

    /**
     * Send "New Ticket Opened" notification to operators.
     */
    public function sendOperatorTicketOpenedEmail(Task $task): bool
    {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Get the template
        $template = $workspace->emailTemplates()
            ->where('type', 'operator_ticket_opened')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return false;
        }

        // Send to workspace owner and admins
        $recipients = $workspace->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->get();

        $sent = false;
        foreach ($recipients as $member) {
            $result = $this->sendTemplateEmail(
                'operator_ticket_opened',
                $task,
                $member->email,
                $member->name
            );
            if ($result) {
                $sent = true;
            }
        }

        return $sent;
    }

    /**
     * Render template with placeholders replaced.
     */
    protected function renderTemplate(string $template, Task $task, Workspace $workspace, ?User $user = null, array $additionalPlaceholders = [], ?User $sender = null): string
    {
        // Get portal URLs - use client portal for inbox workspace clients
        $portalUrl = route('client-portal.login');
        $setPasswordUrl = '';

        if ($user && $user->invitation_token) {
            $setPasswordUrl = route('client-portal.signup', ['token' => $user->invitation_token]);
        }

        // Determine signature - use sender's signature if enabled, otherwise their full name
        $signature = $this->getSignature($sender ?? $task->assignee);

        $placeholders = [
            '{{ticket_id}}' => $task->task_number ?? $task->id,
            '{{ticket_subject}}' => $task->title,
            '{{ticket_status}}' => $task->status?->name ?? 'Open',
            '{{ticket_priority}}' => $task->workspacePriority?->name ?? $task->priority?->label() ?? 'Normal',
            '{{ticket_department}}' => $task->department?->name ?? 'General',
            '{{customer_name}}' => $user?->name ?? $task->creator?->name ?? 'Customer',
            '{{customer_email}}' => $user?->email ?? $task->source_email ?? $task->creator?->email ?? '',
            '{{agent_name}}' => $task->assignee?->name ?? 'Support Team',
            '{{workspace_name}}' => $workspace->name,
            '{{ticket_url}}' => $task->getClientTicketUrl(),
            '{{created_date}}' => $task->created_at->format('M d, Y H:i'),
            '{{sla_due_date}}' => $task->sla_due_at?->format('M d, Y H:i') ?? 'N/A',
            '{{portal_url}}' => $portalUrl,
            '{{set_password_url}}' => $setPasswordUrl,
            '{{signature}}' => $signature,
        ];

        // Merge additional placeholders
        $placeholders = array_merge($placeholders, $additionalPlaceholders);

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );
    }

    /**
     * Get signature for a user.
     * Returns the user's signature if enabled, otherwise their full name.
     */
    protected function getSignature(?User $user): string
    {
        if (!$user) {
            return 'Support Team';
        }

        // Check if user has signature enabled for inbox and has a signature
        if ($user->include_signature_in_inbox && !empty($user->signature)) {
            // Strip HTML tags for plain text emails, but keep the formatted content
            return strip_tags($user->signature);
        }

        // Fall back to full name
        return $user->name ?? 'Support Team';
    }

    /**
     * Send "Status Changed" email to customer.
     */
    public function sendStatusChangedEmail(Task $task, ?string $oldStatusName, ?string $newStatusName): bool
    {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Load creator
        $task->load('creator');
        $creator = $task->creator;

        // Determine recipient - prefer source_email (from email submission), fallback to creator email if guest
        $toEmail = $task->source_email;
        $toName = $creator?->name ?? 'Customer';

        // If no source_email, only send if creator is a guest (not a team member)
        if (!$toEmail) {
            if (!$creator || !$creator->is_guest) {
                Log::info('Status changed email skipped - no source_email and creator is not a guest', [
                    'task_id' => $task->id,
                    'creator_id' => $creator?->id,
                ]);
                return false;
            }
            $toEmail = $creator->email;
        }

        // Get the template from database or use default
        $template = $workspace->emailTemplates()
            ->where('type', 'user_status_changed')
            ->where('is_active', true)
            ->first();

        $subject = null;
        $body = null;

        if ($template) {
            $subject = $template->subject;
            $body = $template->body;
        } else {
            // Use default template
            $defaults = WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults['user_status_changed'])) {
                $subject = $defaults['user_status_changed']['subject'];
                $body = $defaults['user_status_changed']['body'];
            } else {
                Log::warning('Status changed email template not found');
                return false;
            }
        }

        // Additional placeholders for status change
        $additionalPlaceholders = [
            '{{old_status}}' => $oldStatusName ?? 'N/A',
            '{{new_status}}' => $newStatusName ?? 'N/A',
        ];

        // Render the template
        $renderedSubject = $this->renderTemplate($subject, $task, $workspace, $creator, $additionalPlaceholders);
        $renderedBody = $this->renderTemplate($body, $task, $workspace, $creator, $additionalPlaceholders);

        // Get from address
        $fromAddress = $workspace->inbound_email
            ? $workspace->inbound_email
            : config('mail.from.address');
        $fromName = $workspace->name;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $fromAddress, $fromName, $renderedSubject, $renderedBody) {
                $message->to($toEmail, $toName)
                    ->from($fromAddress, $fromName)
                    ->subject($renderedSubject)
                    ->html(nl2br(e($renderedBody)));
            });

            Log::info('Status changed email sent', [
                'task_id' => $task->id,
                'to' => $toEmail,
                'old_status' => $oldStatusName,
                'new_status' => $newStatusName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send status changed email', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send "Assignee Changed" email to customer.
     */
    public function sendAssigneeChangedEmail(Task $task, ?string $newAssigneeName): bool
    {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Load creator
        $task->load('creator');
        $creator = $task->creator;

        // Determine recipient - prefer source_email (from email submission), fallback to creator email if guest
        $toEmail = $task->source_email;
        $toName = $creator?->name ?? 'Customer';

        if (!$toEmail) {
            if (!$creator || !$creator->is_guest) {
                Log::info('Assignee changed email skipped - no source_email and creator is not a guest', [
                    'task_id' => $task->id,
                ]);
                return false;
            }
            $toEmail = $creator->email;
        }

        // Get the template from database or use default
        $template = $workspace->emailTemplates()
            ->where('type', 'user_assignee_changed')
            ->where('is_active', true)
            ->first();

        $subject = null;
        $body = null;

        if ($template) {
            $subject = $template->subject;
            $body = $template->body;
        } else {
            $defaults = WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults['user_assignee_changed'])) {
                $subject = $defaults['user_assignee_changed']['subject'];
                $body = $defaults['user_assignee_changed']['body'];
            } else {
                Log::warning('Assignee changed email template not found');
                return false;
            }
        }

        // Render the template
        $renderedSubject = $this->renderTemplate($subject, $task, $workspace, $creator);
        $renderedBody = $this->renderTemplate($body, $task, $workspace, $creator);

        // Get from address
        $fromAddress = $workspace->inbound_email ?: config('mail.from.address');
        $fromName = $workspace->name;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $fromAddress, $fromName, $renderedSubject, $renderedBody) {
                $message->to($toEmail, $toName)
                    ->from($fromAddress, $fromName)
                    ->subject($renderedSubject)
                    ->html(nl2br(e($renderedBody)));
            });

            Log::info('Assignee changed email sent', [
                'task_id' => $task->id,
                'to' => $toEmail,
                'assignee' => $newAssigneeName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send assignee changed email', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send "New Comment" email to customer.
     */
    public function sendNewCommentEmail(Task $task, string $commentContent, string $commenterName, ?User $sender = null): bool
    {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Load creator
        $task->load('creator');
        $creator = $task->creator;

        // Determine recipient - prefer source_email (from email submission), fallback to creator email if guest
        $toEmail = $task->source_email;
        $toName = $creator?->name ?? 'Customer';

        if (!$toEmail) {
            if (!$creator || !$creator->is_guest) {
                Log::info('New comment email skipped - no source_email and creator is not a guest', [
                    'task_id' => $task->id,
                ]);
                return false;
            }
            $toEmail = $creator->email;
        }

        // Get the template from database or use default
        $template = $workspace->emailTemplates()
            ->where('type', 'user_new_comment')
            ->where('is_active', true)
            ->first();

        $subject = null;
        $body = null;

        if ($template) {
            $subject = $template->subject;
            $body = $template->body;
        } else {
            $defaults = WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults['user_new_comment'])) {
                $subject = $defaults['user_new_comment']['subject'];
                $body = $defaults['user_new_comment']['body'];
            } else {
                Log::warning('New comment email template not found');
                return false;
            }
        }

        // Additional placeholders for comment
        $additionalPlaceholders = [
            '{{comment_content}}' => strip_tags($commentContent),
        ];

        // Render the template - pass sender for signature
        $renderedSubject = $this->renderTemplate($subject, $task, $workspace, $creator, $additionalPlaceholders, $sender);
        $renderedBody = $this->renderTemplate($body, $task, $workspace, $creator, $additionalPlaceholders, $sender);

        // Get from address
        $fromAddress = $workspace->inbound_email ?: config('mail.from.address');
        $fromName = $workspace->name;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $fromAddress, $fromName, $renderedSubject, $renderedBody) {
                $message->to($toEmail, $toName)
                    ->from($fromAddress, $fromName)
                    ->subject($renderedSubject)
                    ->html(nl2br(e($renderedBody)));
            });

            Log::info('New comment email sent', [
                'task_id' => $task->id,
                'to' => $toEmail,
                'commenter' => $commenterName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send new comment email', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send "Department Changed" email to customer.
     */
    public function sendDepartmentChangedEmail(Task $task, ?string $oldDepartmentName, ?string $newDepartmentName): bool
    {
        $workspace = $task->workspace;

        if (!$workspace || $workspace->type->value !== 'inbox') {
            return false;
        }

        // Load creator
        $task->load('creator');
        $creator = $task->creator;

        // Determine recipient - prefer source_email (from email submission), fallback to creator email if guest
        $toEmail = $task->source_email;
        $toName = $creator?->name ?? 'Customer';

        if (!$toEmail) {
            if (!$creator || !$creator->is_guest) {
                Log::info('Department changed email skipped - no source_email and creator is not a guest', [
                    'task_id' => $task->id,
                ]);
                return false;
            }
            $toEmail = $creator->email;
        }

        // Get the template from database or use default
        $template = $workspace->emailTemplates()
            ->where('type', 'user_department_changed')
            ->where('is_active', true)
            ->first();

        $subject = null;
        $body = null;

        if ($template) {
            $subject = $template->subject;
            $body = $template->body;
        } else {
            $defaults = WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults['user_department_changed'])) {
                $subject = $defaults['user_department_changed']['subject'];
                $body = $defaults['user_department_changed']['body'];
            } else {
                Log::warning('Department changed email template not found');
                return false;
            }
        }

        // Additional placeholders for department change
        $additionalPlaceholders = [
            '{{old_department}}' => $oldDepartmentName ?? 'N/A',
            '{{new_department}}' => $newDepartmentName ?? 'N/A',
        ];

        // Render the template
        $renderedSubject = $this->renderTemplate($subject, $task, $workspace, $creator, $additionalPlaceholders);
        $renderedBody = $this->renderTemplate($body, $task, $workspace, $creator, $additionalPlaceholders);

        // Get from address
        $fromAddress = $workspace->inbound_email ?: config('mail.from.address');
        $fromName = $workspace->name;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $fromAddress, $fromName, $renderedSubject, $renderedBody) {
                $message->to($toEmail, $toName)
                    ->from($fromAddress, $fromName)
                    ->subject($renderedSubject)
                    ->html(nl2br(e($renderedBody)));
            });

            Log::info('Department changed email sent', [
                'task_id' => $task->id,
                'to' => $toEmail,
                'old_department' => $oldDepartmentName,
                'new_department' => $newDepartmentName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send department changed email', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

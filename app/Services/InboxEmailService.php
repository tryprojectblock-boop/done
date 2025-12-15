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
    protected function renderTemplate(string $template, Task $task, Workspace $workspace, ?User $user = null): string
    {
        // Get portal URLs
        $portalUrl = route('login');
        $setPasswordUrl = '';

        if ($user && $user->invitation_token) {
            $setPasswordUrl = route('guest.signup', ['token' => $user->invitation_token]);
        }

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
            '{{ticket_url}}' => route('tasks.show', $task),
            '{{created_date}}' => $task->created_at->format('M d, Y H:i'),
            '{{sla_due_date}}' => $task->sla_due_at?->format('M d, Y H:i') ?? 'N/A',
            '{{portal_url}}' => $portalUrl,
            '{{set_password_url}}' => $setPasswordUrl,
        ];

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );
    }
}

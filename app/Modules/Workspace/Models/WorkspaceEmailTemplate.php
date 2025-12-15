<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceEmailTemplate extends Model
{
    protected $fillable = [
        'workspace_id',
        'type',
        'name',
        'subject',
        'body',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Available template types with their descriptions.
     */
    public const TYPES = [
        'ticket_created' => [
            'name' => 'Ticket Created',
            'description' => 'Sent to customer when a new ticket is created',
            'icon' => 'tabler--ticket',
            'color' => 'primary',
        ],
        'ticket_assigned' => [
            'name' => 'Ticket Assigned',
            'description' => 'Sent to agent when a ticket is assigned to them',
            'icon' => 'tabler--user-check',
            'color' => 'info',
        ],
        'ticket_resolved' => [
            'name' => 'Ticket Resolved',
            'description' => 'Sent to customer when their ticket is resolved',
            'icon' => 'tabler--check',
            'color' => 'success',
        ],
        'ticket_reply' => [
            'name' => 'Ticket Reply',
            'description' => 'Sent to customer when an agent replies to their ticket',
            'icon' => 'tabler--message-reply',
            'color' => 'secondary',
        ],
        'sla_warning' => [
            'name' => 'SLA Warning',
            'description' => 'Sent to agent when a ticket is approaching SLA breach',
            'icon' => 'tabler--alert-triangle',
            'color' => 'warning',
        ],
        'sla_breach' => [
            'name' => 'SLA Breach',
            'description' => 'Sent to agent/manager when a ticket has breached SLA',
            'icon' => 'tabler--alert-circle',
            'color' => 'error',
        ],
    ];

    /**
     * Available placeholders for templates.
     */
    public const PLACEHOLDERS = [
        '{{ticket_id}}' => 'Ticket ID/Number',
        '{{ticket_subject}}' => 'Ticket Subject',
        '{{ticket_status}}' => 'Current Ticket Status',
        '{{ticket_priority}}' => 'Ticket Priority',
        '{{ticket_department}}' => 'Ticket Department',
        '{{customer_name}}' => 'Customer Name',
        '{{customer_email}}' => 'Customer Email',
        '{{agent_name}}' => 'Assigned Agent Name',
        '{{workspace_name}}' => 'Workspace Name',
        '{{ticket_url}}' => 'Link to Ticket',
        '{{created_date}}' => 'Ticket Created Date',
        '{{sla_due_date}}' => 'SLA Due Date/Time',
    ];

    /**
     * Default template content.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            'ticket_created' => [
                'name' => 'Default - Ticket Created',
                'subject' => 'Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{customer_name}},\n\nThank you for contacting us. Your ticket has been created successfully.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Department: {{ticket_department}}\n\nOur team will review your request and get back to you as soon as possible.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'ticket_assigned' => [
                'name' => 'Default - Ticket Assigned',
                'subject' => '[Action Required] Ticket #{{ticket_id}} assigned to you',
                'body' => "Hello {{agent_name}},\n\nA new ticket has been assigned to you.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Customer: {{customer_name}}\n\nPlease review and respond to this ticket.\n\nView ticket: {{ticket_url}}",
            ],
            'ticket_resolved' => [
                'name' => 'Default - Ticket Resolved',
                'subject' => 'Ticket #{{ticket_id}} has been resolved',
                'body' => "Hello {{customer_name}},\n\nYour ticket has been marked as resolved.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n\nIf you have any further questions or if the issue persists, please reply to this email or create a new ticket.\n\nThank you for choosing us!\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'ticket_reply' => [
                'name' => 'Default - Ticket Reply',
                'subject' => 'Re: Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{customer_name}},\n\nYou have received a new reply on your ticket.\n\n**Ticket:** #{{ticket_id}} - {{ticket_subject}}\n\nTo view the full conversation and respond, please visit:\n{{ticket_url}}\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'sla_warning' => [
                'name' => 'Default - SLA Warning',
                'subject' => '[SLA Warning] Ticket #{{ticket_id}} approaching deadline',
                'body' => "Hello {{agent_name}},\n\n**SLA Warning:** The following ticket is approaching its SLA deadline.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Customer: {{customer_name}}\n- SLA Due: {{sla_due_date}}\n\nPlease take action immediately to avoid SLA breach.\n\nView ticket: {{ticket_url}}",
            ],
            'sla_breach' => [
                'name' => 'Default - SLA Breach',
                'subject' => '[URGENT] SLA Breach - Ticket #{{ticket_id}}',
                'body' => "**URGENT: SLA Breach Alert**\n\nTicket #{{ticket_id}} has breached its SLA.\n\n**Ticket Details:**\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Customer: {{customer_name}}\n- Assigned To: {{agent_name}}\n- SLA Due: {{sla_due_date}}\n\nImmediate attention required.\n\nView ticket: {{ticket_url}}",
            ],
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the type info (name, description, icon, color).
     */
    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? [
            'name' => ucfirst(str_replace('_', ' ', $this->type)),
            'description' => '',
            'icon' => 'tabler--mail',
            'color' => 'neutral',
        ];
    }

    /**
     * Create default templates for a workspace.
     */
    public static function createDefaults(Workspace $workspace): void
    {
        $defaults = self::getDefaultTemplates();

        foreach ($defaults as $type => $template) {
            self::create([
                'workspace_id' => $workspace->id,
                'type' => $type,
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'is_active' => true,
                'is_default' => true,
            ]);
        }
    }
}

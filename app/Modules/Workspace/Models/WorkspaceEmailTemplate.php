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
     * Template categories.
     */
    public const CATEGORIES = [
        'user' => [
            'name' => 'For User',
            'description' => 'Emails sent to customers/users',
            'icon' => 'tabler--user',
        ],
        'operator' => [
            'name' => 'For Operator',
            'description' => 'Emails sent to agents/operators',
            'icon' => 'tabler--headset',
        ],
        'custom' => [
            'name' => 'Custom',
            'description' => 'Custom email templates',
            'icon' => 'tabler--template',
        ],
    ];

    /**
     * Available template types with their descriptions.
     */
    public const TYPES = [
        // For User templates
        'user_ticket_opened' => [
            'name' => 'New Ticket Opened',
            'description' => 'Sent to customer when a new ticket is created',
            'icon' => 'tabler--ticket',
            'color' => 'primary',
            'category' => 'user',
        ],
        'user_ticket_reply' => [
            'name' => 'New Ticket Reply',
            'description' => 'Sent to customer when an agent replies',
            'icon' => 'tabler--message-reply',
            'color' => 'info',
            'category' => 'user',
        ],
        'user_new_reply' => [
            'name' => 'New User Reply',
            'description' => 'Confirmation when user submits a reply',
            'icon' => 'tabler--send',
            'color' => 'secondary',
            'category' => 'user',
        ],
        'user_ticket_closed' => [
            'name' => 'Ticket Closed',
            'description' => 'Sent to customer when ticket is closed',
            'icon' => 'tabler--check',
            'color' => 'success',
            'category' => 'user',
        ],
        'user_portal_access' => [
            'name' => 'Client Portal Access',
            'description' => 'Sent to customer with login credentials for client portal',
            'icon' => 'tabler--login',
            'color' => 'accent',
            'category' => 'user',
        ],
        'user_status_changed' => [
            'name' => 'Status Changed',
            'description' => 'Sent to customer when ticket status is changed',
            'icon' => 'tabler--refresh',
            'color' => 'warning',
            'category' => 'user',
        ],
        'user_assignee_changed' => [
            'name' => 'Assignee Changed',
            'description' => 'Sent to customer when ticket is assigned to someone',
            'icon' => 'tabler--user-check',
            'color' => 'info',
            'category' => 'user',
        ],
        'user_new_comment' => [
            'name' => 'New Comment',
            'description' => 'Sent to customer when a team member comments on their ticket',
            'icon' => 'tabler--message',
            'color' => 'primary',
            'category' => 'user',
        ],
        'user_department_changed' => [
            'name' => 'Department Changed',
            'description' => 'Sent to customer when ticket is transferred to another department',
            'icon' => 'tabler--building',
            'color' => 'info',
            'category' => 'user',
        ],

        // For Operator templates
        'operator_assigned' => [
            'name' => 'Assigned to Ticket',
            'description' => 'Sent when ticket is assigned to operator',
            'icon' => 'tabler--user-check',
            'color' => 'primary',
            'category' => 'operator',
        ],
        'operator_new_comment' => [
            'name' => 'New Comment Posted',
            'description' => 'Sent when a new comment is posted on ticket',
            'icon' => 'tabler--message',
            'color' => 'info',
            'category' => 'operator',
        ],
        'operator_internal_message' => [
            'name' => 'New Internal Message',
            'description' => 'Sent for internal team messages',
            'icon' => 'tabler--lock',
            'color' => 'warning',
            'category' => 'operator',
        ],
        'operator_internal_ticket' => [
            'name' => 'New Internal Ticket',
            'description' => 'Sent when internal ticket is created',
            'icon' => 'tabler--ticket',
            'color' => 'secondary',
            'category' => 'operator',
        ],
        'operator_ticket_opened' => [
            'name' => 'New Ticket Opened',
            'description' => 'Notification when new ticket arrives',
            'icon' => 'tabler--bell',
            'color' => 'accent',
            'category' => 'operator',
        ],
        'operator_ticket_reply' => [
            'name' => 'New Ticket Reply',
            'description' => 'Notification when customer replies',
            'icon' => 'tabler--message-reply',
            'color' => 'info',
            'category' => 'operator',
        ],

        // Custom templates
        'custom_high_demand' => [
            'name' => 'High Demand',
            'description' => 'Auto-reply during high volume periods',
            'icon' => 'tabler--flame',
            'color' => 'error',
            'category' => 'custom',
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
        '{{portal_url}}' => 'Client Portal Login URL',
        '{{set_password_url}}' => 'Set Password URL',
        '{{old_status}}' => 'Previous Status Name',
        '{{new_status}}' => 'New Status Name',
        '{{comment_content}}' => 'Comment Content',
        '{{old_department}}' => 'Previous Department Name',
        '{{new_department}}' => 'New Department Name',
    ];

    /**
     * Get templates grouped by category.
     */
    public static function getTypesByCategory(): array
    {
        $grouped = [];
        foreach (self::TYPES as $type => $info) {
            $category = $info['category'] ?? 'custom';
            $grouped[$category][$type] = $info;
        }
        return $grouped;
    }

    /**
     * Default template content.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            // For User templates
            'user_ticket_opened' => [
                'name' => 'New Ticket Opened',
                'subject' => 'Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{customer_name}},\n\nThank you for contacting us. Your ticket has been created successfully.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Department: {{ticket_department}}\n\nOur team will review your request and get back to you as soon as possible.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_ticket_reply' => [
                'name' => 'New Ticket Reply',
                'subject' => 'Re: Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{customer_name}},\n\nYou have received a new reply on your ticket.\n\n**Ticket:** #{{ticket_id}} - {{ticket_subject}}\n\nTo view the full conversation and respond, please visit:\n{{ticket_url}}\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_new_reply' => [
                'name' => 'New User Reply',
                'subject' => 'Re: Ticket #{{ticket_id}} - Your reply has been received',
                'body' => "Hello {{customer_name}},\n\nThank you for your reply. We have received your message regarding ticket #{{ticket_id}}.\n\nOur team will review and respond as soon as possible.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_ticket_closed' => [
                'name' => 'Ticket Closed',
                'subject' => 'Ticket #{{ticket_id}} has been closed',
                'body' => "Hello {{customer_name}},\n\nYour ticket has been closed.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n\nIf you have any further questions or if the issue persists, please reply to this email or create a new ticket.\n\nThank you for choosing us!\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_portal_access' => [
                'name' => 'Client Portal Access',
                'subject' => 'Your Client Portal Access - {{workspace_name}}',
                'body' => "Hello {{customer_name}},\n\nYou now have access to our Client Portal where you can view and manage your support tickets.\n\n**Your Login Credentials:**\n- Email: {{customer_email}}\n- Password: Please set your password using the link below\n\n**Set Your Password:**\n{{set_password_url}}\n\n**Access Client Portal:**\n{{portal_url}}\n\nOnce you've set your password, you can log in to view all your tickets, track progress, and communicate with our support team.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_status_changed' => [
                'name' => 'Status Changed',
                'subject' => 'Ticket #{{ticket_id}} - Status Updated to {{new_status}}',
                'body' => "Hello {{customer_name}},\n\nThe status of your ticket has been updated.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Previous Status: {{old_status}}\n- New Status: {{new_status}}\n\nYou can view your ticket at:\n{{ticket_url}}\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_assignee_changed' => [
                'name' => 'Assignee Changed',
                'subject' => 'Ticket #{{ticket_id}} - Now being handled by {{agent_name}}',
                'body' => "Hello {{customer_name}},\n\nYour ticket has been assigned to a team member who will assist you.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Assigned To: {{agent_name}}\n\nYou can reply to this email to add more information to your ticket.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_new_comment' => [
                'name' => 'New Comment',
                'subject' => 'Re: Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{customer_name}},\n\nA team member has responded to your ticket.\n\n**Ticket:** #{{ticket_id}} - {{ticket_subject}}\n\n---\n{{comment_content}}\n---\n\nYou can reply directly to this email to respond.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],
            'user_department_changed' => [
                'name' => 'Department Changed',
                'subject' => 'Ticket #{{ticket_id}} - Transferred to {{new_department}}',
                'body' => "Hello {{customer_name}},\n\nYour ticket has been transferred to a different department for better assistance.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- New Department: {{new_department}}\n\nThe new team will review your ticket and respond as soon as possible.\n\nYou can reply to this email to add more information.\n\nBest regards,\n{{workspace_name}} Support Team",
            ],

            // For Operator templates
            'operator_assigned' => [
                'name' => 'Assigned to Ticket',
                'subject' => '[Action Required] Ticket #{{ticket_id}} assigned to you',
                'body' => "Hello {{agent_name}},\n\nA ticket has been assigned to you.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Customer: {{customer_name}}\n\nPlease review and respond to this ticket.\n\nView ticket: {{ticket_url}}",
            ],
            'operator_new_comment' => [
                'name' => 'New Comment Posted',
                'subject' => 'New comment on Ticket #{{ticket_id}}',
                'body' => "Hello {{agent_name}},\n\nA new comment has been posted on ticket #{{ticket_id}}.\n\n**Ticket:** {{ticket_subject}}\n\nView ticket: {{ticket_url}}",
            ],
            'operator_internal_message' => [
                'name' => 'New Internal Message',
                'subject' => '[Internal] New message on Ticket #{{ticket_id}}',
                'body' => "Hello {{agent_name}},\n\nA new internal message has been posted on ticket #{{ticket_id}}.\n\n**Ticket:** {{ticket_subject}}\n\nThis is an internal note not visible to the customer.\n\nView ticket: {{ticket_url}}",
            ],
            'operator_internal_ticket' => [
                'name' => 'New Internal Ticket',
                'subject' => '[Internal] New internal ticket #{{ticket_id}}',
                'body' => "Hello {{agent_name}},\n\nA new internal ticket has been created.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n\nView ticket: {{ticket_url}}",
            ],
            'operator_ticket_opened' => [
                'name' => 'New Ticket Opened',
                'subject' => '[New Ticket] #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello,\n\nA new ticket has been received.\n\n**Ticket Details:**\n- Ticket ID: #{{ticket_id}}\n- Subject: {{ticket_subject}}\n- Priority: {{ticket_priority}}\n- Customer: {{customer_name}}\n- Department: {{ticket_department}}\n\nView ticket: {{ticket_url}}",
            ],
            'operator_ticket_reply' => [
                'name' => 'New Ticket Reply',
                'subject' => '[Customer Reply] Ticket #{{ticket_id}} - {{ticket_subject}}',
                'body' => "Hello {{agent_name}},\n\nThe customer has replied to ticket #{{ticket_id}}.\n\n**Ticket:** {{ticket_subject}}\n**Customer:** {{customer_name}}\n\nView ticket: {{ticket_url}}",
            ],

            // Custom templates
            'custom_high_demand' => [
                'name' => 'High Demand',
                'subject' => 'Ticket #{{ticket_id}} - We received your request',
                'body' => "Hello {{customer_name}},\n\nThank you for contacting us. We're currently experiencing higher than normal volume.\n\n**Your Ticket:** #{{ticket_id}} - {{ticket_subject}}\n\nOur team is working hard to respond to all inquiries. We appreciate your patience and will get back to you as soon as possible.\n\nBest regards,\n{{workspace_name}} Support Team",
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
     * Create default templates for a workspace (only creates missing templates).
     */
    public static function createDefaults(Workspace $workspace): void
    {
        $defaults = self::getDefaultTemplates();
        $existingTypes = $workspace->emailTemplates()->pluck('type')->toArray();

        foreach ($defaults as $type => $template) {
            if (!in_array($type, $existingTypes)) {
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
}

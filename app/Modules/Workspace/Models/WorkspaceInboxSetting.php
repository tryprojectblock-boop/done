<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInboxSetting extends Model
{
    protected $fillable = [
        'workspace_id',
        'from_email',
        'inbound_email',
        'inbound_email_prefix',
        'email_verified',
        'email_verified_at',
        'hour_format',
        'date_format',
        'timezone',
        'working_hours_configured_at',
        'departments_configured_at',
        'priorities_configured_at',
        'holidays_configured_at',
        'sla_configured_at',
        'ticket_rules_configured_at',
        'sla_rules_configured_at',
        'idle_ticket_hours',
        'idle_ticket_reply_status_id',
        'idle_rules_configured_at',
        'email_templates_configured_at',
        'client_portal_enabled',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'working_hours_configured_at' => 'datetime',
        'departments_configured_at' => 'datetime',
        'priorities_configured_at' => 'datetime',
        'holidays_configured_at' => 'datetime',
        'sla_configured_at' => 'datetime',
        'ticket_rules_configured_at' => 'datetime',
        'sla_rules_configured_at' => 'datetime',
        'idle_ticket_hours' => 'integer',
        'idle_rules_configured_at' => 'datetime',
        'email_templates_configured_at' => 'datetime',
        'client_portal_enabled' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function idleTicketReplyStatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WorkflowStatus::class, 'idle_ticket_reply_status_id');
    }

    /**
     * Check if the inbox is fully configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->inbound_email) && $this->email_verified;
    }

    /**
     * Get total weekly working hours for the workspace.
     */
    public function getTotalWeeklyHours(): float
    {
        return $this->workspace->workingHours()
            ->where('is_enabled', true)
            ->sum('total_hours');
    }
}

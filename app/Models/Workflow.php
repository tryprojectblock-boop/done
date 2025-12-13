<?php

namespace App\Models;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    /**
     * Text color constants for status badges.
     */
    private const TEXT_WHITE = '#ffffff';
    private const TEXT_BLACK = '#000000';

    /**
     * Built-in workflow template names.
     */
    public const BUILTIN_WORKFLOWS = [
        'Basic Task Tracking',
        'Bug Tracking',
    ];

    /**
     * Workflow types.
     */
    public const TYPE_CLASSIC = 'classic';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_INBOX = 'inbox';

    /**
     * Workflow type labels.
     */
    public const TYPES = [
        self::TYPE_CLASSIC => [
            'label' => 'Classic Workflow',
            'description' => 'Best for Classic Workspace - simple Open/Closed workflow',
            'workspace' => 'Classic',
        ],
        self::TYPE_PRODUCT => [
            'label' => 'Product Workflow',
            'description' => 'Best for Product Workspace - includes Backlog for planning',
            'workspace' => 'Product',
        ],
        self::TYPE_INBOX => [
            'label' => 'Inbox Workflow',
            'description' => 'Best for Inbox Workspace - includes Unassigned for triage',
            'workspace' => 'Inbox',
        ],
    ];

    /**
     * Extended color palette for statuses.
     */
    public const COLORS = [
        'slate' => ['label' => 'Slate', 'bg' => '#64748b', 'text' => self::TEXT_WHITE],
        'gray' => ['label' => 'Gray', 'bg' => '#6b7280', 'text' => self::TEXT_WHITE],
        'red' => ['label' => 'Red', 'bg' => '#ef4444', 'text' => self::TEXT_WHITE],
        'orange' => ['label' => 'Orange', 'bg' => '#f97316', 'text' => self::TEXT_WHITE],
        'amber' => ['label' => 'Amber', 'bg' => '#f59e0b', 'text' => self::TEXT_BLACK],
        'yellow' => ['label' => 'Yellow', 'bg' => '#eab308', 'text' => self::TEXT_BLACK],
        'lime' => ['label' => 'Lime', 'bg' => '#84cc16', 'text' => self::TEXT_BLACK],
        'green' => ['label' => 'Green', 'bg' => '#22c55e', 'text' => self::TEXT_WHITE],
        'emerald' => ['label' => 'Emerald', 'bg' => '#10b981', 'text' => self::TEXT_WHITE],
        'teal' => ['label' => 'Teal', 'bg' => '#14b8a6', 'text' => self::TEXT_WHITE],
        'cyan' => ['label' => 'Cyan', 'bg' => '#06b6d4', 'text' => self::TEXT_BLACK],
        'sky' => ['label' => 'Sky', 'bg' => '#0ea5e9', 'text' => self::TEXT_WHITE],
        'blue' => ['label' => 'Blue', 'bg' => '#3b82f6', 'text' => self::TEXT_WHITE],
        'indigo' => ['label' => 'Indigo', 'bg' => '#6366f1', 'text' => self::TEXT_WHITE],
        'violet' => ['label' => 'Violet', 'bg' => '#8b5cf6', 'text' => self::TEXT_WHITE],
        'purple' => ['label' => 'Purple', 'bg' => '#a855f7', 'text' => self::TEXT_WHITE],
        'fuchsia' => ['label' => 'Fuchsia', 'bg' => '#d946ef', 'text' => self::TEXT_WHITE],
        'pink' => ['label' => 'Pink', 'bg' => '#ec4899', 'text' => self::TEXT_WHITE],
        'rose' => ['label' => 'Rose', 'bg' => '#f43f5e', 'text' => self::TEXT_WHITE],
    ];

    protected $fillable = [
        'company_id',
        'workspace_id',
        'name',
        'description',
        'type',
        'is_default',
        'is_archived',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the workflow.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the workspace that owns the workflow.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the user who created the workflow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the statuses for this workflow.
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(WorkflowStatus::class)->orderBy('sort_order');
    }

    /**
     * Get active statuses only.
     */
    public function activeStatuses(): HasMany
    {
        return $this->hasMany(WorkflowStatus::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Check if workflow has at least one active status.
     */
    public function hasActiveStatus(): bool
    {
        return $this->statuses()->where('is_active', true)->exists();
    }

    /**
     * Get count of active statuses.
     */
    public function getActiveStatusCountAttribute(): int
    {
        return $this->statuses()->where('is_active', true)->count();
    }

    /**
     * Get count of inactive statuses.
     */
    public function getInactiveStatusCountAttribute(): int
    {
        return $this->statuses()->where('is_active', false)->count();
    }

    /**
     * Duplicate this workflow with all its statuses.
     */
    public function duplicate(?int $createdBy = null): self
    {
        $newWorkflow = $this->replicate();
        $newWorkflow->name = $this->name . ' (Copy)';
        $newWorkflow->is_default = false;
        $newWorkflow->created_by = $createdBy;
        $newWorkflow->save();

        foreach ($this->statuses as $status) {
            $newStatus = $status->replicate();
            $newStatus->workflow_id = $newWorkflow->id;
            $newStatus->created_by = $createdBy;
            $newStatus->save();
        }

        return $newWorkflow;
    }

    /**
     * Check if this is a built-in workflow.
     */
    public function isBuiltIn(): bool
    {
        return in_array($this->name, self::BUILTIN_WORKFLOWS);
    }

    /**
     * Archive the workflow.
     */
    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }

    /**
     * Restore the workflow from archive.
     */
    public function restore(): void
    {
        $this->update(['is_archived' => false]);
    }

    /**
     * Create default statuses for a new workflow based on type.
     */
    public function createDefaultStatuses(?int $createdBy = null): void
    {
        $workflowType = $this->type ?? self::TYPE_CLASSIC;

        switch ($workflowType) {
            case self::TYPE_PRODUCT:
                $this->createProductWorkflowStatuses($createdBy);
                break;
            case self::TYPE_INBOX:
                $this->createInboxWorkflowStatuses($createdBy);
                break;
            case self::TYPE_CLASSIC:
            default:
                $this->createClassicWorkflowStatuses($createdBy);
                break;
        }
    }

    /**
     * Create statuses for Classic workflow (Open, Closed).
     */
    protected function createClassicWorkflowStatuses(?int $createdBy = null): void
    {
        // Open status (active)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Open',
            'color' => 'blue',
            'description' => 'New tasks start here',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_OPEN,
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Closed status (inactive)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Closed',
            'color' => 'slate',
            'description' => 'Completed tasks',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_CLOSED,
            'sort_order' => 999,
            'is_active' => false,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create statuses for Product workflow (Backlog, Open, Closed).
     */
    protected function createProductWorkflowStatuses(?int $createdBy = null): void
    {
        // Backlog status (active) - like Open but named Backlog for product planning
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Backlog',
            'color' => 'purple',
            'description' => 'Items waiting to be worked on',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_OPEN,
            'sort_order' => 0,
            'is_active' => true,
            'responsibility' => WorkflowStatus::RESPONSIBILITY_CREATOR,
            'created_by' => $createdBy,
        ]);

        // Open status (active)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Open',
            'color' => 'blue',
            'description' => 'Tasks being actively worked on',
            'is_default' => false,
            'type' => WorkflowStatus::TYPE_ACTIVE,
            'sort_order' => 1,
            'is_active' => true,
            'responsibility' => WorkflowStatus::RESPONSIBILITY_ASSIGNEE,
            'created_by' => $createdBy,
        ]);

        // Closed status (inactive)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Closed',
            'color' => 'slate',
            'description' => 'Completed tasks',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_CLOSED,
            'sort_order' => 999,
            'is_active' => false,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create statuses for Inbox workflow (Unassigned, Open, Closed).
     */
    protected function createInboxWorkflowStatuses(?int $createdBy = null): void
    {
        // Unassigned status (active) - like Open but named Unassigned for inbox triage
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Unassigned',
            'color' => 'orange',
            'description' => 'Items waiting to be assigned',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_OPEN,
            'sort_order' => 0,
            'is_active' => true,
            'responsibility' => WorkflowStatus::RESPONSIBILITY_CREATOR,
            'created_by' => $createdBy,
        ]);

        // Open status (active)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Open',
            'color' => 'blue',
            'description' => 'Tasks being worked on',
            'is_default' => false,
            'type' => WorkflowStatus::TYPE_ACTIVE,
            'sort_order' => 1,
            'is_active' => true,
            'responsibility' => WorkflowStatus::RESPONSIBILITY_ASSIGNEE,
            'created_by' => $createdBy,
        ]);

        // Closed status (inactive)
        WorkflowStatus::create([
            'workflow_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'name' => 'Closed',
            'color' => 'slate',
            'description' => 'Completed tasks',
            'is_default' => true,
            'type' => WorkflowStatus::TYPE_CLOSED,
            'sort_order' => 999,
            'is_active' => false,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Get the workflow type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? 'Classic Workflow';
    }

    /**
     * Scope for active (non-archived) workflows.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for archived workflows.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope for workflows belonging to a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for built-in workflows.
     */
    public function scopeBuiltIn($query)
    {
        return $query->where('is_default', true);
    }
}

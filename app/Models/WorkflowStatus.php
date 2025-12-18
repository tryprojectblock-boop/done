<?php

namespace App\Models;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStatus extends Model
{
    use HasFactory;

    /**
     * Status types.
     */
    public const TYPE_OPEN = 'open';
    public const TYPE_ACTIVE = 'active';
    public const TYPE_CLOSED = 'closed';

    /**
     * Responsibility types.
     */
    public const RESPONSIBILITY_CREATOR = 'creator';
    public const RESPONSIBILITY_ASSIGNEE = 'assignee';

    public const RESPONSIBILITIES = [
        self::RESPONSIBILITY_CREATOR => 'Creator',
        self::RESPONSIBILITY_ASSIGNEE => 'Assignee',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'workflow_id',
        'workspace_id',
        'name',
        'color',
        'description',
        'is_default',
        'type',
        'sort_order',
        'is_active',
        'responsibility',
        'allowed_transitions',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'allowed_transitions' => 'array',
        ];
    }

    /**
     * Get the workflow that owns the status.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the workspace that owns the status.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the user who created the status.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is the Open status.
     */
    public function isOpen(): bool
    {
        return $this->type === self::TYPE_OPEN;
    }

    /**
     * Check if this is the Closed status.
     */
    public function isClosed(): bool
    {
        return $this->type === self::TYPE_CLOSED;
    }

    /**
     * Check if this status is editable (name, color can be changed).
     */
    public function isEditable(): bool
    {
        return true; // All statuses can be edited now
    }

    /**
     * Check if this status can be deleted.
     */
    public function isDeletable(): bool
    {
        // Cannot delete if it's the only active status
        if ($this->is_active && $this->workflow) {
            $activeCount = $this->workflow->statuses()->where('is_active', true)->count();
            if ($activeCount <= 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if this status is sortable.
     */
    public function isSortable(): bool
    {
        return true;
    }

    /**
     * Get the background color for this status.
     */
    public function getBackgroundColorAttribute(): string
    {
        return Workflow::COLORS[$this->color]['bg'] ?? '#6b7280';
    }

    /**
     * Get the text color for this status.
     */
    public function getTextColorAttribute(): string
    {
        return Workflow::COLORS[$this->color]['text'] ?? '#ffffff';
    }

    /**
     * Get the next sort order for statuses in a workflow.
     */
    public static function getNextSortOrder(int $workflowId): int
    {
        $maxOrder = self::where('workflow_id', $workflowId)
            ->where('type', '!=', self::TYPE_CLOSED)
            ->max('sort_order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Get the responsibility label.
     */
    public function getResponsibilityLabelAttribute(): string
    {
        return self::RESPONSIBILITIES[$this->responsibility] ?? 'Assignee';
    }

    /**
     * Check if transition to target status is allowed.
     * If no rules are set (null), all transitions are allowed.
     */
    public function canTransitionTo(int $targetStatusId): bool
    {
        // If no rules set, allow all transitions
        if ($this->allowed_transitions === null) {
            return true;
        }

        return in_array($targetStatusId, $this->allowed_transitions);
    }

    /**
     * Get the statuses this status can transition to.
     */
    public function getAllowedTransitionStatuses()
    {
        if ($this->allowed_transitions === null) {
            // Return all other statuses in the workflow
            return $this->workflow->statuses()->where('id', '!=', $this->id)->get();
        }

        return self::whereIn('id', $this->allowed_transitions)->get();
    }

    /**
     * Check if status rules are configured.
     */
    public function hasTransitionRules(): bool
    {
        return $this->allowed_transitions !== null && count($this->allowed_transitions) > 0;
    }
}

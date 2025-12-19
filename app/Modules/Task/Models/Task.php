<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use App\Models\User;
use App\Models\WorkflowStatus;
use App\Modules\Auth\Models\Company;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskType;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceDepartment;
use App\Modules\Workspace\Models\WorkspacePriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'company_id',
        'task_number',
        'title',
        'description',
        'type',
        'priority',
        'progress',
        'status_id',
        'assignee_id',
        'created_by',
        'due_date',
        'start_date',
        'completed_at',
        'closed_at',
        'closed_by',
        'parent_task_id',
        'parent_link_notes',
        'estimated_time',
        'actual_time',
        'position',
        'is_private',
        'is_on_hold',
        'hold_reason',
        'hold_by',
        'hold_at',
        'milestone_id',
        'google_event_id',
        'google_synced_at',
        'google_sync_source',
        'source',
        'source_email',
        'custom_fields',
        'client_token',
        'department_id',
        'workspace_priority_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'array', // Now stores array of type values
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'start_date' => 'date',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
            'estimated_time' => 'integer',
            'actual_time' => 'integer',
            'position' => 'integer',
            'is_private' => 'boolean',
            'is_on_hold' => 'boolean',
            'hold_at' => 'datetime',
            'google_synced_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    /**
     * Get task types as TaskType enum instances
     */
    public function getTypesAttribute(): array
    {
        $types = $this->type ?? [];
        return array_filter(array_map(function ($value) {
            return TaskType::tryFrom($value);
        }, $types));
    }

    /**
     * Check if task has a specific type
     */
    public function hasType(TaskType|string $type): bool
    {
        $typeValue = $type instanceof TaskType ? $type->value : $type;
        return in_array($typeValue, $this->type ?? []);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Task $task) {
            if (empty($task->uuid)) {
                $task->uuid = (string) Str::uuid();
            }
            if (empty($task->task_number)) {
                $task->task_number = static::generateTaskNumber($task->workspace_id);
            }
        });

        // Sync to Google Calendar when task is saved with a due date
        static::saved(function (Task $task) {
            // Only trigger if due_date exists and task is not closed
            if ($task->due_date && !$task->closed_at) {
                // Dispatch sync job (non-blocking)
                dispatch(function () use ($task) {
                    $googleService = app(\App\Services\GoogleCalendarService::class);

                    // Get sync user (assignee or creator)
                    $syncUser = null;
                    if ($task->assignee_id) {
                        $syncUser = \App\Models\User::find($task->assignee_id);
                    }
                    if (!$syncUser?->canSyncGoogleCalendar() && $task->created_by) {
                        $syncUser = \App\Models\User::find($task->created_by);
                    }

                    if ($syncUser?->canSyncGoogleCalendar()) {
                        $googleService->syncTaskToGoogle($task->fresh(), $syncUser);
                    }
                })->afterResponse();
            }
        });
    }

    /**
     * Generate a unique task number for the workspace.
     * Returns a 3-digit zero-padded number (e.g., 001, 002, 003).
     */
    public static function generateTaskNumber(int|string $workspaceId): string
    {
        $lastTask = static::where('workspace_id', $workspaceId)
            ->orderByRaw('CAST(task_number AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastTask && is_numeric($lastTask->task_number)) {
            $nextNumber = (int) $lastTask->task_number + 1;
        }

        return str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(WorkspaceDepartment::class, 'department_id');
    }

    public function workspacePriority(): BelongsTo
    {
        return $this->belongsTo(WorkspacePriority::class, 'workspace_priority_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Milestone::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function holdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hold_by');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag')
            ->withTimestamps();
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_watchers')
            ->withPivot('added_by')
            ->withTimestamps();
    }

    public function discussions(): BelongsToMany
    {
        return $this->belongsToMany(Discussion::class, 'discussion_tasks')
            ->withPivot('linked_by')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->orderBy('created_at', 'desc');
    }

    // ==================== HELPER METHODS ====================

    public function isOpen(): bool
    {
        return $this->status?->isOpen() ?? true;
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null || ($this->status?->isClosed() ?? false);
    }

    public function isOnHold(): bool
    {
        return $this->is_on_hold === true;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isClosed();
    }

    public function isSubtask(): bool
    {
        return $this->parent_task_id !== null || $this->hasType(TaskType::SUBTASK);
    }

    public function isOwner(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isAssignee(User $user): bool
    {
        return $this->assignee_id === $user->id;
    }

    public function isWatcher(User $user): bool
    {
        return $this->watchers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user can manage the on-hold state of this task.
     * Only task creator, assignee, and admins can put tasks on hold or resume them.
     */
    public function canManageHold(User $user): bool
    {
        // Task creator can manage hold
        if ($this->isOwner($user)) {
            return true;
        }

        // Task assignee can manage hold
        if ($this->isAssignee($user)) {
            return true;
        }

        // Admins and owners can manage hold
        if ($user->isAdminOrHigher()) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view this task.
     * Users can view if they are: workspace member, watcher, assignee, creator, or mentioned.
     */
    public function canView(User $user): bool
    {
        // Watchers can always view (they were explicitly added or mentioned)
        if ($this->isWatcher($user)) {
            return true;
        }

        // Assignee can always view
        if ($this->isAssignee($user)) {
            return true;
        }

        // Creator can always view
        if ($this->isOwner($user)) {
            return true;
        }

        // Public tasks - standard visibility (workspace members or company members)
        if (!$this->is_private) {
            // If task belongs to a workspace, check workspace membership
            if ($this->workspace_id) {
                return $this->workspace && $this->workspace->hasMember($user);
            }
            // Otherwise check company membership
            return $this->company_id === $user->company_id;
        }

        // Private tasks - check workspace role (owner/admin can view all private tasks)
        if ($this->workspace_id && $this->workspace) {
            // Workspace owner can always view
            if ($this->workspace->isOwner($user)) {
                return true;
            }

            // Workspace admin can always view
            $workspaceRole = $this->workspace->getMemberRole($user);
            if ($workspaceRole && $workspaceRole->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user is mentioned in any comment on this task.
     */
    public function isMentionedInComments(User $user): bool
    {
        // Get all comments for this task
        $comments = $this->comments()->get();

        foreach ($comments as $comment) {
            // Check if user ID is mentioned in the comment content
            // Quill mentions format: data-id="USER_ID" or data-mention-id="USER_ID"
            if (preg_match('/data-(?:mention-)?id=["\']' . $user->id . '["\']/', $comment->content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all user IDs mentioned in comments.
     */
    public function getMentionedUserIds(): array
    {
        $mentionedIds = [];
        $comments = $this->comments()->get();

        foreach ($comments as $comment) {
            // Extract user IDs from mentions in comment content
            preg_match_all('/data-(?:mention-)?id=["\'](\d+)["\']/', $comment->content, $matches);
            if (!empty($matches[1])) {
                $mentionedIds = array_merge($mentionedIds, $matches[1]);
            }
        }

        return array_unique(array_map('intval', $mentionedIds));
    }

    public function canChangeStatus(User $user): bool
    {
        // Assignee and owner can change status
        if ($this->isOwner($user) || $this->isAssignee($user) || $user->isAdminOrHigher()) {
            return true;
        }

        // Workspace members can change status
        if ($this->workspace && $this->workspace->hasMember($user)) {
            return true;
        }

        return false;
    }

    public function canClose(User $user): bool
    {
        // Only owner or admin can close the task
        if ($this->isOwner($user) || $user->isAdminOrHigher()) {
            return true;
        }

        // Workspace admins/owners can close tasks
        if ($this->workspace && $this->workspace->hasMember($user)) {
            $role = $this->workspace->getMemberRole($user);
            if ($role && ($role->isOwner() || $role->isAdmin())) {
                return true;
            }
        }

        return false;
    }

    public function canEdit(User $user): bool
    {
        // Owner, assignee, or admin can edit
        if ($this->isOwner($user) || $this->isAssignee($user) || $user->isAdminOrHigher()) {
            return true;
        }

        // Workspace members can edit tasks in their workspace
        if ($this->workspace && $this->workspace->hasMember($user)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can inline edit task details (Status, Assignee, Type, Priority, Due Date).
     * For public tasks in a workspace:
     *   - Workspace owner/admin can inline edit
     *   - Assignee can inline edit
     *   - Regular workspace members cannot inline edit
     * For private tasks, the creator and assignee can inline edit.
     */
    public function canInlineEdit(User $user): bool
    {
        // For tasks in a workspace, check workspace role (not system role)
        if ($this->workspace_id && $this->workspace) {
            // Workspace owner can always inline edit
            if ($this->workspace->isOwner($user)) {
                return true;
            }

            // Workspace admin can always inline edit
            $workspaceRole = $this->workspace->getMemberRole($user);
            if ($workspaceRole && $workspaceRole->isAdmin()) {
                return true;
            }

            // For public tasks, only assignee can inline edit
            if (!$this->is_private) {
                return $this->isAssignee($user);
            }

            // For private tasks, creator and assignee can inline edit
            return $this->isOwner($user) || $this->isAssignee($user);
        }

        // For tasks without workspace, use standard edit permission
        return $this->canEdit($user);
    }

    // ==================== SCOPES ====================

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWatchedBy($query, int $userId)
    {
        return $query->whereHas('watchers', fn ($q) => $q->where('user_id', $userId));
    }

    public function scopeWithStatus($query, int $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeWithType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNull('closed_at');
    }

    public function scopeDueBetween($query, $start, $end)
    {
        return $query->whereBetween('due_date', [$start, $end]);
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('closed_at');
    }

    public function scopeClosed($query)
    {
        return $query->whereNotNull('closed_at');
    }

    public function scopeParentTasks($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeSubtasksOf($query, int $parentId)
    {
        return $query->where('parent_task_id', $parentId);
    }

    public function scopeWithTag($query, int $tagId)
    {
        return $query->whereHas('tags', fn ($q) => $q->where('tag_id', $tagId));
    }

    public function scopeCreatedWithinDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeUpdatedWithinDays($query, int $days)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    /**
     * Generate or get the client token for this task.
     * Used for public client ticket view access.
     */
    public function getOrCreateClientToken(): string
    {
        if (!$this->client_token) {
            $this->client_token = Str::random(64);
            $this->save();
        }

        return $this->client_token;
    }

    /**
     * Get the client ticket view URL.
     */
    public function getClientTicketUrl(): string
    {
        return route('client.ticket.show', [
            'task' => $this->uuid,
            'token' => $this->getOrCreateClientToken(),
        ]);
    }

    /**
     * Scope to filter tasks visible to a user.
     * Private tasks are only visible to workspace owner/admin, creator, assignee, or watchers.
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Public tasks
            $q->where('is_private', false)
            // OR private tasks where user has access
            ->orWhere(function ($privateQuery) use ($user) {
                $privateQuery->where('is_private', true)
                    ->where(function ($accessQuery) use ($user) {
                        // User is creator
                        $accessQuery->where('created_by', $user->id)
                            // OR user is assignee
                            ->orWhere('assignee_id', $user->id)
                            // OR user is watcher
                            ->orWhereHas('watchers', fn ($w) => $w->where('user_id', $user->id))
                            // OR user is workspace owner
                            ->orWhereHas('workspace', fn ($ws) => $ws->where('owner_id', $user->id))
                            // OR user is workspace admin
                            ->orWhereHas('workspace.members', fn ($m) => $m->where('workspace_members.user_id', $user->id)->where('workspace_members.role', 'admin'));
                    });
            });
        });
    }
}

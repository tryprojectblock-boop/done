<?php

namespace App\Models;

use App\Modules\Auth\Models\Company;
use App\Modules\Task\Models\Tag;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'company_id',
        'title',
        'description',
        'start_date',
        'due_date',
        'progress',
        'owner_id',
        'created_by',
        'priority',
        'status',
        'color',
        'settings',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'progress' => 'integer',
            'settings' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Milestone $milestone) {
            if (empty($milestone->uuid)) {
                $milestone->uuid = (string) Str::uuid();
            }
        });

        // Auto-update status based on dates and progress
        static::saving(function (Milestone $milestone) {
            // If progress is 100%, mark as completed (unless blocked)
            if ($milestone->progress >= 100 && $milestone->status !== 'completed' && $milestone->status !== 'blocked') {
                $milestone->status = 'completed';
                $milestone->completed_at = now();
            }

            // If was completed but progress changed below 100%, reopen
            if ($milestone->progress < 100 && $milestone->status === 'completed') {
                $milestone->status = 'in_progress';
                $milestone->completed_at = null;
            }

            // Don't auto-change blocked status - it's manually set
            if ($milestone->status === 'blocked') {
                return;
            }

            // Check if overdue (past due date and not completed)
            // Note: We keep 'delayed' as internal status for overdue items
            if ($milestone->due_date && $milestone->due_date->isPast() && $milestone->status !== 'completed') {
                // Mark as delayed/overdue but don't change if blocked
                $milestone->status = 'delayed';
            }
        });
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'milestone_tag')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MilestoneComment::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MilestoneAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MilestoneActivity::class)->orderBy('created_at', 'desc');
    }

    // ==================== COMPUTED ATTRIBUTES ====================

    /**
     * Calculate progress based on completed tasks.
     */
    public function calculateProgress(): int
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            return $this->progress; // Return manual progress if no tasks
        }

        $completedTasks = $this->tasks()->whereNotNull('closed_at')->count();

        return (int) round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Recalculate and save progress based on tasks.
     */
    public function recalculateProgress(): void
    {
        $this->update(['progress' => $this->calculateProgress()]);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'blocked' => 'Blocked',
            'completed' => 'Completed',
            'delayed' => 'Delayed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'not_started' => '#6b7280', // gray
            'in_progress' => '#f59e0b', // amber
            'blocked' => '#ef4444', // red
            'completed' => '#10b981', // green
            'delayed' => '#dc2626', // darker red
            default => '#6b7280', // gray
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'badge-ghost',
            'in_progress' => 'badge-warning',
            'blocked' => 'badge-error',
            'completed' => 'badge-success',
            'delayed' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => '#10b981', // green
            'medium' => '#f59e0b', // amber
            'high' => '#ef4444', // red
            default => '#6b7280', // gray
        };
    }

    /**
     * Get task statistics.
     */
    public function getTaskStatsAttribute(): array
    {
        $tasks = $this->tasks;
        $total = $tasks->count();
        $completed = $tasks->whereNotNull('closed_at')->count();
        $open = $total - $completed;

        return [
            'total' => $total,
            'completed' => $completed,
            'open' => $open,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    // ==================== HELPER METHODS ====================

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isDelayed(): bool
    {
        return $this->status === 'delayed';
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isCreator(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function canEdit(User $user): bool
    {
        return $this->isCreator($user) || $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isCreator($user) || $user->isAdminOrHigher();
    }

    /**
     * Get days remaining until due date.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
    }

    /**
     * Log an activity.
     */
    public function logActivity(User $user, string $action, string $description, ?array $changes = null): MilestoneActivity
    {
        return $this->activities()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
        ]);
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

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDelayed($query)
    {
        return $query->where('status', 'delayed');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    public function scopeDueBetween($query, $start, $end)
    {
        return $query->whereBetween('due_date', [$start, $end]);
    }

    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc');
    }

    // ==================== STATIC METHODS ====================

    public static function statuses(): array
    {
        return [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'blocked' => 'Blocked',
            'completed' => 'Completed',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }
}

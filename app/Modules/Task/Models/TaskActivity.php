<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use App\Models\User;
use App\Modules\Task\Enums\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    use HasFactory;

    protected $table = 'task_activities';

    protected $fillable = [
        'task_id',
        'user_id',
        'type',
        'old_value',
        'new_value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, ActivityType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPER METHODS ====================

    public function getFormattedDescription(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $userName = $this->user?->name ?? 'Someone';

        return match ($this->type) {
            ActivityType::CREATED => "{$userName} created this task",
            ActivityType::UPDATED => "{$userName} updated this task",
            ActivityType::STATUS_CHANGED => $this->formatStatusChange($userName),
            ActivityType::PRIORITY_CHANGED => $this->formatPriorityChange($userName),
            ActivityType::ASSIGNEE_CHANGED => $this->formatAssigneeChange($userName),
            ActivityType::DUE_DATE_CHANGED => $this->formatDueDateChange($userName),
            ActivityType::DESCRIPTION_CHANGED => "{$userName} updated the description",
            ActivityType::TITLE_CHANGED => $this->formatTitleChange($userName),
            ActivityType::TAG_ADDED => $this->formatTagAdded($userName),
            ActivityType::TAG_REMOVED => $this->formatTagRemoved($userName),
            ActivityType::WATCHER_ADDED => $this->formatWatcherAdded($userName),
            ActivityType::WATCHER_REMOVED => $this->formatWatcherRemoved($userName),
            ActivityType::ATTACHMENT_ADDED => $this->formatAttachmentAdded($userName),
            ActivityType::ATTACHMENT_REMOVED => $this->formatAttachmentRemoved($userName),
            ActivityType::COMMENT_ADDED => "{$userName} added a comment",
            ActivityType::COMMENT_EDITED => "{$userName} edited a comment",
            ActivityType::COMMENT_DELETED => "{$userName} deleted a comment",
            ActivityType::CLOSED => "{$userName} closed this task",
            ActivityType::REOPENED => "{$userName} reopened this task",
            ActivityType::PARENT_CHANGED => $this->formatParentChange($userName),
            ActivityType::PUT_ON_HOLD => $this->formatPutOnHold($userName),
            ActivityType::RESUMED => "{$userName} resumed this task",
            ActivityType::DEPARTMENT_CHANGED => $this->formatDepartmentChange($userName),
            ActivityType::WORKSPACE_PRIORITY_CHANGED => $this->formatWorkspacePriorityChange($userName),
            ActivityType::TYPE_CHANGED => $this->formatTypeChange($userName),
            default => "{$userName} made changes to this task",
        };
    }

    private function formatStatusChange(string $userName): string
    {
        $oldStatus = $this->old_value['name'] ?? 'unknown';
        $newStatus = $this->new_value['name'] ?? 'unknown';
        return "{$userName} changed status from \"{$oldStatus}\" to \"{$newStatus}\"";
    }

    private function formatPriorityChange(string $userName): string
    {
        $oldPriority = $this->old_value['label'] ?? 'unknown';
        $newPriority = $this->new_value['label'] ?? 'unknown';
        return "{$userName} changed priority from {$oldPriority} to {$newPriority}";
    }

    private function formatAssigneeChange(string $userName): string
    {
        $oldAssignee = $this->old_value['name'] ?? null;
        $newAssignee = $this->new_value['name'] ?? null;

        if (!$oldAssignee && $newAssignee) {
            return "{$userName} assigned this task to {$newAssignee}";
        }
        if ($oldAssignee && !$newAssignee) {
            return "{$userName} unassigned {$oldAssignee} from this task";
        }
        return "{$userName} reassigned this task from {$oldAssignee} to {$newAssignee}";
    }

    private function formatDueDateChange(string $userName): string
    {
        $oldDate = $this->old_value['date'] ?? null;
        $newDate = $this->new_value['date'] ?? null;

        if (!$oldDate && $newDate) {
            return "{$userName} set due date to {$newDate}";
        }
        if ($oldDate && !$newDate) {
            return "{$userName} removed the due date";
        }
        return "{$userName} changed due date from {$oldDate} to {$newDate}";
    }

    private function formatTitleChange(string $userName): string
    {
        $oldTitle = $this->old_value['title'] ?? 'unknown';
        $newTitle = $this->new_value['title'] ?? 'unknown';
        return "{$userName} changed title from \"{$oldTitle}\" to \"{$newTitle}\"";
    }

    private function formatTagAdded(string $userName): string
    {
        $tagName = $this->new_value['name'] ?? 'a tag';
        return "{$userName} added tag \"{$tagName}\"";
    }

    private function formatTagRemoved(string $userName): string
    {
        $tagName = $this->old_value['name'] ?? 'a tag';
        return "{$userName} removed tag \"{$tagName}\"";
    }

    private function formatWatcherAdded(string $userName): string
    {
        $watcherName = $this->new_value['name'] ?? 'a user';
        return "{$userName} added {$watcherName} as a watcher";
    }

    private function formatWatcherRemoved(string $userName): string
    {
        $watcherName = $this->old_value['name'] ?? 'a user';
        return "{$userName} removed {$watcherName} from watchers";
    }

    private function formatAttachmentAdded(string $userName): string
    {
        $fileName = $this->new_value['name'] ?? 'a file';
        return "{$userName} attached \"{$fileName}\"";
    }

    private function formatAttachmentRemoved(string $userName): string
    {
        $fileName = $this->old_value['name'] ?? 'a file';
        return "{$userName} removed attachment \"{$fileName}\"";
    }

    private function formatParentChange(string $userName): string
    {
        $oldParent = $this->old_value['task_number'] ?? null;
        $newParent = $this->new_value['task_number'] ?? null;

        if (!$oldParent && $newParent) {
            return "{$userName} linked this as subtask of {$newParent}";
        }
        if ($oldParent && !$newParent) {
            return "{$userName} unlinked this task from {$oldParent}";
        }
        return "{$userName} moved this task from {$oldParent} to {$newParent}";
    }

    private function formatPutOnHold(string $userName): string
    {
        $reason = $this->new_value['reason'] ?? null;
        if ($reason) {
            return "{$userName} put this task on hold: \"{$reason}\"";
        }
        return "{$userName} put this task on hold";
    }

    private function formatDepartmentChange(string $userName): string
    {
        $oldDepartment = $this->old_value['name'] ?? null;
        $newDepartment = $this->new_value['name'] ?? null;

        if (!$oldDepartment && $newDepartment) {
            return "{$userName} assigned to department \"{$newDepartment}\"";
        }
        if ($oldDepartment && !$newDepartment) {
            return "{$userName} removed from department \"{$oldDepartment}\"";
        }
        return "{$userName} moved from \"{$oldDepartment}\" to \"{$newDepartment}\"";
    }

    private function formatWorkspacePriorityChange(string $userName): string
    {
        $oldPriority = $this->old_value['name'] ?? null;
        $newPriority = $this->new_value['name'] ?? null;

        if (!$oldPriority && $newPriority) {
            return "{$userName} set priority to \"{$newPriority}\"";
        }
        if ($oldPriority && !$newPriority) {
            return "{$userName} removed priority";
        }
        return "{$userName} changed priority from \"{$oldPriority}\" to \"{$newPriority}\"";
    }

    private function formatTypeChange(string $userName): string
    {
        $oldTypes = $this->old_value['types'] ?? [];
        $newTypes = $this->new_value['types'] ?? [];

        $oldStr = implode(', ', $oldTypes) ?: 'none';
        $newStr = implode(', ', $newTypes) ?: 'none';

        return "{$userName} changed type from [{$oldStr}] to [{$newStr}]";
    }

    // ==================== FACTORY METHODS ====================

    public static function log(
        Task $task,
        User $user,
        ActivityType $type,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $description = null
    ): self {
        return static::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => $type,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
        ]);
    }
}

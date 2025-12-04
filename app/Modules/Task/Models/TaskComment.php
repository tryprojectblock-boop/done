<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TaskComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'task_id',
        'user_id',
        'content',
        'parent_id',
        'edited_at',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TaskComment $comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskCommentAttachment::class, 'comment_id');
    }

    // ==================== SCOPES ====================

    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    // ==================== HELPER METHODS ====================

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canEdit(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

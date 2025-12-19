<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use App\Modules\Discussion\Enums\DiscussionType;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Discussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'workspace_id',
        'created_by',
        'title',
        'details',
        'type',
        'is_public',
        'comments_count',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DiscussionType::class,
            'is_public' => 'boolean',
            'comments_count' => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Discussion $discussion) {
            if (empty($discussion->uuid)) {
                $discussion->uuid = (string) Str::uuid();
            }
            if (empty($discussion->last_activity_at)) {
                $discussion->last_activity_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discussion_participants')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class)
            ->orderBy('created_at', 'desc');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'discussion_tasks')
            ->withPivot('linked_by')
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Public discussions in user's company
            $q->where(function ($sub) use ($user) {
                $sub->where('company_id', $user->company_id)
                    ->where('is_public', true);
            })
            // Or user is creator
            ->orWhere('created_by', $user->id)
            // Or user is a participant
            ->orWhereHas('participants', function ($sub) use ($user) {
                $sub->where('user_id', $user->id);
            });
        });
    }

    // ==================== HELPER METHODS ====================

    public function isCreator(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function canView(User $user): bool
    {
        // Admin/Owner can view all in their company
        if ($user->isAdminOrHigher() && $this->company_id === $user->company_id) {
            return true;
        }

        // Public discussions - anyone in the company can view
        if ($this->is_public && $this->company_id === $user->company_id) {
            return true;
        }

        // Creator can always view
        if ($this->isCreator($user)) {
            return true;
        }

        // Participants can view
        return $this->isParticipant($user);
    }

    public function canComment(User $user): bool
    {
        return $this->canView($user);
    }

    public function canEdit(User $user): bool
    {
        return $this->isCreator($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isCreator($user) || $user->isAdminOrHigher();
    }

    public function canInvite(User $user): bool
    {
        return $this->isCreator($user) || $user->isAdminOrHigher();
    }

    public function addParticipant(User $user, ?User $invitedBy = null): void
    {
        if (!$this->isParticipant($user)) {
            $this->participants()->attach($user->id, [
                'invited_by' => $invitedBy?->id,
            ]);
        }
    }

    public function removeParticipant(User $user): void
    {
        $this->participants()->detach($user->id);
    }

    public function updateCommentsCount(): void
    {
        $this->update([
            'comments_count' => $this->allComments()->count(),
        ]);
    }

    public function updateLastActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
        ]);
    }

    public function isPrivate(): bool
    {
        return !$this->is_public;
    }
}

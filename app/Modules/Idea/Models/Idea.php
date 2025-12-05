<?php

declare(strict_types=1);

namespace App\Modules\Idea\Models;

use App\Models\User;
use App\Modules\Idea\Enums\IdeaPriority;
use App\Modules\Idea\Enums\IdeaStatus;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Idea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'workspace_id',
        'created_by',
        'title',
        'short_description',
        'description',
        'status',
        'priority',
        'votes_count',
        'comments_count',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => IdeaStatus::class,
            'priority' => IdeaPriority::class,
            'reviewed_at' => 'datetime',
            'votes_count' => 'integer',
            'comments_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Idea $idea) {
            if (empty($idea->uuid)) {
                $idea->uuid = (string) Str::uuid();
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'idea_members')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(IdeaComment::class)
            ->orderBy('created_at', 'desc');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(IdeaVote::class);
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

    public function scopeWithStatus($query, IdeaStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNew($query)
    {
        return $query->where('status', IdeaStatus::NEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', IdeaStatus::APPROVED);
    }

    // ==================== HELPER METHODS ====================

    public function isOwner(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function canEdit(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canChangeStatus(User $user): bool
    {
        return $user->isAdminOrHigher();
    }

    public function hasVoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function getUserVote(User $user): ?int
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();
        return $vote?->vote;
    }

    public function addMember(User $user, ?User $invitedBy = null): void
    {
        if (!$this->isMember($user)) {
            $this->members()->attach($user->id, [
                'invited_by' => $invitedBy?->id,
            ]);
        }
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function updateVotesCount(): void
    {
        $this->update([
            'votes_count' => $this->votes()->sum('vote'),
        ]);
    }

    public function updateCommentsCount(): void
    {
        $this->update([
            'comments_count' => $this->allComments()->count(),
        ]);
    }

    public function markAsReviewed(User $reviewer, IdeaStatus $status): void
    {
        $this->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);
    }
}

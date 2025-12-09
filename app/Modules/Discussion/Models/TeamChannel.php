<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TeamChannel extends Model
{
    use HasFactory, SoftDeletes;

    // Channel status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVE = 'archive';

    protected $fillable = [
        'uuid',
        'company_id',
        'created_by',
        'name',
        'tag',
        'description',
        'color',
        'status',
        'members_count',
        'threads_count',
        'last_activity_at',
    ];

    protected $casts = [
        'members_count' => 'integer',
        'threads_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamChannel $channel) {
            if (empty($channel->uuid)) {
                $channel->uuid = (string) Str::uuid();
            }
            if (empty($channel->last_activity_at)) {
                $channel->last_activity_at = now();
            }
            // Ensure tag starts with #
            if (!empty($channel->tag) && !str_starts_with($channel->tag, '#')) {
                $channel->tag = '#' . $channel->tag;
            }
        });

        static::updating(function (TeamChannel $channel) {
            // Ensure tag starts with #
            if (!empty($channel->tag) && !str_starts_with($channel->tag, '#')) {
                $channel->tag = '#' . $channel->tag;
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_channel_members', 'channel_id', 'user_id')
            ->withPivot(['role', 'invited_by', 'joined_at'])
            ->withTimestamps();
    }

    public function threads(): HasMany
    {
        return $this->hasMany(TeamChannelThread::class, 'channel_id');
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(TeamChannelJoinRequest::class, 'channel_id');
    }

    public function pendingJoinRequests(): HasMany
    {
        return $this->hasMany(TeamChannelJoinRequest::class, 'channel_id')
            ->where('status', TeamChannelJoinRequest::STATUS_PENDING);
    }

    // ==================== SCOPES ====================

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVE);
    }

    public function scopeNotArchived($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);
    }

    /**
     * Get channels accessible by the user (member of the channel).
     * Only members can see channels now.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                // Admin/Owner can see all channels
                if ($user->isAdminOrHigher()) {
                    return;
                }
                // Regular users can only see channels they are a member of
                $q->whereHas('members', function ($memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->id);
                })
                // Or channels created by the user
                ->orWhere('created_by', $user->id);
            });
    }

    /**
     * Get channels visible to the user in the listing.
     * Only channels where user is a member will be shown.
     * Admin/Owner can see all channels.
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                // Admin/Owner can see all channels
                if ($user->isAdminOrHigher()) {
                    return;
                }
                // Regular users can only see channels they are a member of
                $q->whereHas('members', function ($memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->id);
                })
                // Or channels created by the user
                ->orWhere('created_by', $user->id);
            });
    }

    // ==================== PERMISSIONS ====================

    public function canView(User $user): bool
    {
        // Must be in same company
        if ($user->company_id !== $this->company_id) {
            return false;
        }

        // Admin/Owner can see all
        if ($user->isAdminOrHigher()) {
            return true;
        }

        // Creator can always view
        if ($user->id === $this->created_by) {
            return true;
        }

        // Only members can view channels
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function canManage(User $user): bool
    {
        // Must be in same company
        if ($user->company_id !== $this->company_id) {
            return false;
        }

        // Only Admin/Owner can manage channels
        return $user->isAdminOrHigher();
    }

    public function canPost(User $user): bool
    {
        // Can only post to active channels
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        return $this->canView($user);
    }

    /**
     * Check if the channel is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the channel is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if the channel is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVE;
    }

    // ==================== MEMBER MANAGEMENT ====================

    public function addMember(User $user, ?User $invitedBy = null, string $role = 'member'): void
    {
        if (!$this->members()->where('user_id', $user->id)->exists()) {
            $this->members()->attach($user->id, [
                'invited_by' => $invitedBy?->id,
                'role' => $role,
                'joined_at' => now(),
            ]);
            $this->updateMembersCount();

            // Send notification to the added member (if inviter is different from user)
            if ($invitedBy && $invitedBy->id !== $user->id) {
                app(NotificationService::class)->createChannelMemberAddedNotification(
                    $user,
                    $invitedBy,
                    $this
                );
            }
        }
    }

    public function removeMember(User $user): void
    {
        // Can't remove creator
        if ($user->id === $this->created_by) {
            return;
        }

        $this->members()->detach($user->id);
        $this->updateMembersCount();
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user can access (click/open) this channel.
     * Only members, creator, or admin/owner can access.
     */
    public function canAccess(User $user): bool
    {
        if ($user->company_id !== $this->company_id) {
            return false;
        }

        // Admin/Owner can access all channels
        if ($user->isAdminOrHigher()) {
            return true;
        }

        // Creator can always access
        if ($user->id === $this->created_by) {
            return true;
        }

        // Check if user is a member
        return $this->isMember($user);
    }

    public function hasPendingJoinRequest(User $user): bool
    {
        return $this->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', TeamChannelJoinRequest::STATUS_PENDING)
            ->exists();
    }

    public function getPendingJoinRequest(User $user): ?TeamChannelJoinRequest
    {
        return $this->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', TeamChannelJoinRequest::STATUS_PENDING)
            ->first();
    }

    // ==================== COUNTERS ====================

    public function updateMembersCount(): void
    {
        $this->update(['members_count' => $this->members()->count()]);
    }

    public function updateThreadsCount(): void
    {
        $this->update(['threads_count' => $this->threads()->count()]);
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    // ==================== ACCESSORS ====================

    public function getColorClassAttribute(): string
    {
        return match ($this->color) {
            'primary' => 'bg-blue-500/10 text-blue-500',
            'secondary' => 'bg-purple-500/10 text-purple-500',
            'accent' => 'bg-pink-500/10 text-pink-500',
            'info' => 'bg-cyan-500/10 text-cyan-500',
            'success' => 'bg-green-500/10 text-green-500',
            'warning' => 'bg-yellow-500/10 text-yellow-500',
            'error' => 'bg-red-500/10 text-red-500',
            'orange' => 'bg-orange-500/10 text-orange-500',
            'teal' => 'bg-teal-500/10 text-teal-500',
            'indigo' => 'bg-indigo-500/10 text-indigo-500',
            'gray' => 'bg-gray-500/10 text-gray-500',
            default => 'bg-blue-500/10 text-blue-500',
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'primary' => 'badge-primary',
            'secondary' => 'badge-secondary',
            'accent' => 'badge-accent',
            'info' => 'badge-info',
            'success' => 'badge-success',
            'warning' => 'badge-warning',
            'error' => 'badge-error',
            'orange' => 'bg-orange-500 text-white',
            'teal' => 'bg-teal-500 text-white',
            'indigo' => 'bg-indigo-500 text-white',
            'gray' => 'bg-gray-500 text-white',
            default => 'badge-primary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_INACTIVE => 'badge-warning',
            self::STATUS_ARCHIVE => 'badge-neutral',
            default => 'badge-success',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ARCHIVE => 'Archived',
            default => 'Active',
        };
    }
}

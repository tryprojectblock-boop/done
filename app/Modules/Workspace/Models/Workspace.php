<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use App\Models\Workflow;
use App\Modules\Core\Support\BaseModel;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Task\Models\Task;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasUuid;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Enums\WorkspaceStatus;
use App\Modules\Workspace\Enums\WorkspaceType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends BaseModel
{
    use HasUuid;
    use BelongsToTenant;

    protected $table = 'workspaces';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'settings',
        'enabled_modules',
        'owner_id',
        'workflow_id',
        'logo_path',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'type' => WorkspaceType::class,
            'status' => WorkspaceStatus::class,
            'settings' => 'array',
            'enabled_modules' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = str($workspace->name)->slug();
            }

            if (empty($workspace->enabled_modules) && $workspace->type) {
                $workspace->enabled_modules = $workspace->type->defaultModules();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot(['role', 'joined_at', 'invited_by', 'permissions'])
            ->withTimestamps()
            ->using(WorkspaceMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_guests')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    /**
     * Check if a user has guest access to this workspace.
     */
    public function hasGuest(User $user): bool
    {
        return $this->guests()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a guest to this workspace.
     */
    public function addGuest(User $user, ?User $invitedBy = null): void
    {
        if (!$this->hasGuest($user)) {
            $this->guests()->attach($user->id, [
                'invited_by' => $invitedBy?->id,
            ]);
        }
    }

    /**
     * Remove a guest from this workspace.
     */
    public function removeGuest(User $user): void
    {
        $this->guests()->detach($user->id);
    }

    /**
     * Check if user has any access (member or guest).
     */
    public function hasAccess(User $user): bool
    {
        return $this->hasMember($user) || $this->hasGuest($user);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', WorkspaceStatus::ACTIVE);
    }

    public function scopeOfType($query, WorkspaceType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getMemberRole(User $user): ?WorkspaceRole
    {
        $member = $this->members()->where('user_id', $user->id)->first();

        if (! $member) {
            return null;
        }

        return WorkspaceRole::from($member->pivot->role);
    }

    public function addMember(User $user, WorkspaceRole $role = WorkspaceRole::MEMBER, ?User $invitedBy = null): void
    {
        $this->members()->attach($user->id, [
            'role' => $role->value,
            'joined_at' => now(),
            'invited_by' => $invitedBy?->id,
        ]);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function updateMemberRole(User $user, WorkspaceRole $role): void
    {
        $this->members()->updateExistingPivot($user->id, [
            'role' => $role->value,
        ]);
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->enabled_modules ?? []);
    }

    public function enableModule(string $module): void
    {
        $modules = $this->enabled_modules ?? [];

        if (! in_array($module, $modules)) {
            $modules[] = $module;
            $this->update(['enabled_modules' => $modules]);
        }
    }

    public function disableModule(string $module): void
    {
        $modules = $this->enabled_modules ?? [];
        $modules = array_filter($modules, fn ($m) => $m !== $module);
        $this->update(['enabled_modules' => array_values($modules)]);
    }

    public function isClassic(): bool
    {
        return $this->type === WorkspaceType::CLASSIC;
    }

    public function isProduct(): bool
    {
        return $this->type === WorkspaceType::PRODUCT;
    }

    public function isActive(): bool
    {
        return $this->status === WorkspaceStatus::ACTIVE;
    }

    public function archive(): void
    {
        $this->update(['status' => WorkspaceStatus::ARCHIVED]);
    }

    public function restore(): void
    {
        $this->update(['status' => WorkspaceStatus::ACTIVE]);
    }

    public function getLogoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return file_upload()->getUrl($this->logo_path);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use App\Modules\Workspace\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkspaceMember extends Pivot
{
    protected $table = 'workspace_members';

    public $incrementing = true;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'role' => WorkspaceRole::class,
            'permissions' => 'array',
            'joined_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function hasPermission(string $permission): bool
    {
        // Check role-based permissions first
        if (in_array($permission, $this->role->permissions())) {
            return true;
        }

        // Check custom permissions
        return in_array($permission, $this->permissions ?? []);
    }

    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];

        if (! in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn ($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    public function isOwner(): bool
    {
        return $this->role === WorkspaceRole::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === WorkspaceRole::ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === WorkspaceRole::MEMBER;
    }

    public function isGuest(): bool
    {
        return $this->role === WorkspaceRole::GUEST;
    }

    public function canManageMembers(): bool
    {
        return $this->role->canManageMembers();
    }
}

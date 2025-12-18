<?php

namespace App\Models;

use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * User roles with hierarchy levels.
     */
    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';
    public const ROLE_GUEST = 'guest';

    public const ROLES = [
        self::ROLE_OWNER => [
            'label' => 'Owner',
            'level' => 4,
            'color' => 'error',
            'description' => 'Full access to all workspace modules, billing, and user management',
            'permissions' => [
                'manage_billing' => true,
                'manage_subscription' => true,
                'delete_workspace' => true,
                'change_workspace_type' => true,
                'manage_workspace_settings' => true,
                'create_projects' => true,
                'edit_projects' => true,
                'delete_projects' => true,
                'invite_owners' => true,
                'invite_admins' => true,
                'invite_members' => true,
                'invite_guests' => true,
                'remove_owners' => true,
                'remove_admins' => true,
                'remove_members' => true,
                'remove_guests' => true,
                'access_private_items' => true,
                'export_data' => true,
                'import_data' => true,
                'create_templates' => true,
                'create_automation' => true,
                'manage_all_tasks' => true,
                'view_all_projects' => true,
            ],
        ],
        self::ROLE_ADMIN => [
            'label' => 'Admin',
            'level' => 3,
            'color' => 'warning',
            'description' => 'Manage projects, users (except billing), and workspace settings',
            'permissions' => [
                'manage_billing' => false,
                'manage_subscription' => false,
                'delete_workspace' => false,
                'change_workspace_type' => false,
                'manage_workspace_settings' => true,
                'create_projects' => true,
                'edit_projects' => true,
                'delete_projects' => true,
                'invite_owners' => false,
                'invite_admins' => false,
                'invite_members' => true,
                'invite_guests' => true,
                'remove_owners' => false,
                'remove_admins' => false,
                'remove_members' => true,
                'remove_guests' => true,
                'access_private_items' => true,
                'export_data' => true,
                'import_data' => true,
                'create_templates' => true,
                'create_automation' => true,
                'manage_all_tasks' => true,
                'view_all_projects' => true,
            ],
        ],
        self::ROLE_MEMBER => [
            'label' => 'Member',
            'level' => 2,
            'color' => 'info',
            'description' => 'Day-to-day contributor with access to assigned projects',
            'permissions' => [
                'manage_billing' => false,
                'manage_subscription' => false,
                'delete_workspace' => false,
                'change_workspace_type' => false,
                'manage_workspace_settings' => false,
                'create_projects' => false,
                'edit_projects' => false,
                'delete_projects' => false,
                'invite_owners' => false,
                'invite_admins' => false,
                'invite_members' => false,
                'invite_guests' => false,
                'remove_owners' => false,
                'remove_admins' => false,
                'remove_members' => false,
                'remove_guests' => false,
                'access_private_items' => false,
                'export_data' => false,
                'import_data' => false,
                'create_templates' => false,
                'create_automation' => false,
                'manage_all_tasks' => false,
                'view_all_projects' => false,
                // Member-specific permissions
                'create_tasks' => true,
                'edit_own_tasks' => true,
                'add_comments' => true,
                'upload_files' => true,
                'join_discussions' => true,
                'view_assigned_projects' => true,
                'submit_feedback' => true,
            ],
        ],
        self::ROLE_GUEST => [
            'label' => 'Guest',
            'level' => 1,
            'color' => 'neutral',
            'description' => 'Limited access to explicitly shared projects only',
            'permissions' => [
                // Cannot do any of these
                'manage_billing' => false,
                'manage_subscription' => false,
                'delete_workspace' => false,
                'change_workspace_type' => false,
                'manage_workspace_settings' => false,
                'create_projects' => false,
                'edit_projects' => false,
                'delete_projects' => false,
                'invite_owners' => false,
                'invite_admins' => false,
                'invite_members' => false,
                'invite_guests' => false,
                'remove_owners' => false,
                'remove_admins' => false,
                'remove_members' => false,
                'remove_guests' => false,
                'access_private_items' => false,
                'access_private_lists' => false,
                'access_private_documents' => false,
                'access_roadmaps' => false,
                'export_data' => false,
                'import_data' => false,
                'create_templates' => false,
                'create_automation' => false,
                'manage_all_tasks' => false,
                'view_all_projects' => false,
                // Guest-specific permissions (limited to shared scope only)
                'view_invited_projects' => true,
                'view_shared_projects' => true,
                'view_limited_dashboard' => true,
                'add_comments' => true,
                'upload_files' => true,
                'download_files' => true,
                'interact_assigned_tasks' => true,
                'interact_assigned_threads' => true,
            ],
        ],
    ];

    /**
     * User statuses.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INVITED = 'invited';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'company_id',
        'avatar_path',
        'description',
        'role',
        'status',
        'is_guest',
        'guest_company_name',
        'guest_position',
        'guest_phone',
        'guest_notes',
        'invited_by',
        'invited_at',
        'invitation_token',
        'invitation_expires_at',
        'timezone',
        'settings',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'google_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_calendar_id',
        'google_connected_at',
        'signature',
        'include_signature_in_inbox',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'google_access_token',
        'google_refresh_token',
    ];

    /**
     * Default attribute values.
     *
     * @var array
     */
    protected $attributes = [
        'timezone' => 'UTC',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'invited_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'is_guest' => 'boolean',
            'settings' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'google_token_expires_at' => 'datetime',
            'google_connected_at' => 'datetime',
            'include_signature_in_inbox' => 'boolean',
        ];
    }

    /**
     * Bootstrap the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user's primary company (for backward compatibility).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all companies this user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('role', 'is_primary', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get all workspaces the user is a member of.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members', 'user_id', 'workspace_id')
            ->withPivot(['role', 'joined_at', 'invited_by', 'permissions'])
            ->withTimestamps();
    }

    /**
     * Get the user's role in a specific company.
     */
    public function getRoleInCompany(Company $company): ?string
    {
        $membership = $this->companies()->where('company_id', $company->id)->first();
        return $membership?->pivot->role;
    }

    /**
     * Check if user belongs to a company.
     */
    public function belongsToCompany(Company $company): bool
    {
        return $this->companies()->where('company_id', $company->id)->exists();
    }

    /**
     * Get pending team invitations for this user.
     */
    public function pendingTeamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'user_id')
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Get all team invitations for this user.
     */
    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'user_id');
    }

    /**
     * Get workspaces where this user is added as a guest.
     */
    public function guestWorkspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_guests')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    /**
     * Get the user who invited this guest.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if user is a guest in a specific workspace.
     */
    public function isGuestOf(Workspace $workspace): bool
    {
        return $this->guestWorkspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    /**
     * Check if user has any guest access (to any workspace).
     */
    public function hasGuestAccess(): bool
    {
        return $this->guestWorkspaces()->exists();
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $first = substr($this->first_name ?? '', 0, 1);
        $last = substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last) ?: 'U';
    }

    /**
     * Get the role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role]['label'] ?? ucfirst($this->role ?? 'Member');
    }

    /**
     * Get the role color for badges.
     */
    public function getRoleColorAttribute(): string
    {
        return self::ROLES[$this->role]['color'] ?? 'neutral';
    }

    /**
     * Check if user is owner.
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is admin or higher.
     */
    public function isAdminOrHigher(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Check if user is guest.
     */
    public function isGuest(): bool
    {
        return $this->role === self::ROLE_GUEST;
    }

    /**
     * Check if user can manage other users.
     */
    public function canManageUsers(): bool
    {
        return $this->isAdminOrHigher();
    }

    /**
     * Check if user can manage a specific user based on role hierarchy.
     */
    public function canManage(User $user): bool
    {
        if (!$this->canManageUsers()) {
            return false;
        }

        $myLevel = self::ROLES[$this->role]['level'] ?? 0;
        $theirLevel = self::ROLES[$user->role]['level'] ?? 0;

        return $myLevel > $theirLevel;
    }

    /**
     * Check if this user is the company owner (first user who created the company).
     */
    public function isCompanyOwner(): bool
    {
        if (!$this->company) {
            return false;
        }

        return $this->company->owner_id === $this->id;
    }

    /**
     * Check if this user can be deleted.
     * Company owners cannot be deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->isCompanyOwner();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $rolePermissions = self::ROLES[$this->role]['permissions'] ?? [];
        return $rolePermissions[$permission] ?? false;
    }

    /**
     * Check if user can manage billing.
     */
    public function canManageBilling(): bool
    {
        return $this->hasPermission('manage_billing');
    }

    /**
     * Check if user can manage subscription.
     */
    public function canManageSubscription(): bool
    {
        return $this->hasPermission('manage_subscription');
    }

    /**
     * Check if user can delete workspace.
     */
    public function canDeleteWorkspace(): bool
    {
        return $this->hasPermission('delete_workspace');
    }

    /**
     * Check if user can manage workspace settings.
     */
    public function canManageWorkspaceSettings(): bool
    {
        return $this->hasPermission('manage_workspace_settings');
    }

    /**
     * Check if user can create projects.
     */
    public function canCreateProjects(): bool
    {
        return $this->hasPermission('create_projects');
    }

    /**
     * Check if user can edit projects.
     */
    public function canEditProjects(): bool
    {
        return $this->hasPermission('edit_projects');
    }

    /**
     * Check if user can delete projects.
     */
    public function canDeleteProjects(): bool
    {
        return $this->hasPermission('delete_projects');
    }

    /**
     * Check if user can invite a specific role.
     */
    public function canInviteRole(string $role): bool
    {
        return $this->hasPermission('invite_' . $role . 's');
    }

    /**
     * Check if user can remove a specific role.
     */
    public function canRemoveRole(string $role): bool
    {
        return $this->hasPermission('remove_' . $role . 's');
    }

    /**
     * Check if user can access private items.
     */
    public function canAccessPrivateItems(): bool
    {
        return $this->hasPermission('access_private_items');
    }

    /**
     * Check if user can export data.
     */
    public function canExportData(): bool
    {
        return $this->hasPermission('export_data');
    }

    /**
     * Check if user can create templates.
     */
    public function canCreateTemplates(): bool
    {
        return $this->hasPermission('create_templates');
    }

    /**
     * Check if user can view all projects.
     */
    public function canViewAllProjects(): bool
    {
        return $this->hasPermission('view_all_projects');
    }

    /**
     * Check if user is a member role.
     */
    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    /**
     * Get avatar URL.
     * Returns uploaded avatar or generates a default one using UI Avatars.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            // Use CDN endpoint for DigitalOcean Spaces
            $cdnEndpoint = config('filesystems.disks.do_spaces.url');
            if ($cdnEndpoint) {
                return rtrim($cdnEndpoint, '/') . '/' . $this->avatar_path;
            }
            return \Storage::disk('do_spaces')->url($this->avatar_path);
        }

        // Generate default avatar using UI Avatars
        $name = urlencode($this->name ?? $this->email ?? 'User');
        $background = $this->getAvatarBackgroundColor();

        return "https://ui-avatars.com/api/?name={$name}&background={$background}&color=ffffff&size=128&bold=true";
    }

    /**
     * Get a consistent background color based on user ID or email.
     */
    protected function getAvatarBackgroundColor(): string
    {
        $colors = [
            '6366f1', // indigo
            '8b5cf6', // violet
            'ec4899', // pink
            'f43f5e', // rose
            'ef4444', // red
            'f97316', // orange
            'eab308', // yellow
            '22c55e', // green
            '14b8a6', // teal
            '06b6d4', // cyan
            '3b82f6', // blue
        ];

        // Use a hash of the email or ID to get a consistent color
        $hash = crc32($this->email ?? $this->id ?? 'default');
        $index = abs($hash) % count($colors);

        return $colors[$index];
    }

    /**
     * Check if user has a custom avatar uploaded.
     */
    public function hasCustomAvatar(): bool
    {
        return !empty($this->avatar_path);
    }

    /**
     * Get user's notifications.
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get user's unread notifications.
     */
    public function unreadNotifications(): HasMany
    {
        return $this->appNotifications()->whereNull('read_at');
    }

    /**
     * Get unread notification count.
     */
    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Out of Office Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get user's out of office settings.
     */
    public function outOfOffice(): HasOne
    {
        return $this->hasOne(UserOutOfOffice::class)->where('is_active', true)->latest();
    }

    /**
     * Check if user is currently out of office.
     */
    public function isOutOfOffice(): bool
    {
        $ooo = $this->outOfOffice;
        return $ooo && $ooo->isCurrentlyActive();
    }

    /**
     * Get the current out of office entry if active.
     */
    public function getCurrentOutOfOffice(): ?UserOutOfOffice
    {
        $ooo = $this->outOfOffice;
        if ($ooo && $ooo->isCurrentlyActive()) {
            return $ooo;
        }
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user has 2FA enabled and confirmed.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && !empty($this->two_factor_confirmed_at);
    }

    /**
     * Check if user has started 2FA setup but not confirmed.
     */
    public function hasTwoFactorPending(): bool
    {
        return !empty($this->two_factor_secret) && empty($this->two_factor_confirmed_at);
    }

    /**
     * Check if user's company requires 2FA.
     */
    public function requiresTwoFactor(): bool
    {
        if (!$this->company) {
            return false;
        }

        $settings = $this->company->settings ?? [];
        return $settings['two_factor_enabled'] ?? false;
    }

    /**
     * Check if user needs to set up 2FA (company requires it but user hasn't set it up).
     */
    public function needsTwoFactorSetup(): bool
    {
        return $this->requiresTwoFactor() && !$this->hasTwoFactorEnabled();
    }

    /**
     * Get decrypted recovery codes.
     */
    public function getRecoveryCodes(): array
    {
        if (empty($this->two_factor_recovery_codes)) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }

    /**
     * Set encrypted recovery codes.
     */
    public function setRecoveryCodes(array $codes): void
    {
        $this->two_factor_recovery_codes = encrypt(json_encode($codes));
        $this->save();
    }

    /**
     * Use a recovery code.
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getRecoveryCodes();
        $key = array_search($code, $codes);

        if ($key !== false) {
            unset($codes[$key]);
            $this->setRecoveryCodes(array_values($codes));
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Google Calendar Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user has connected their Google account.
     */
    public function hasGoogleConnected(): bool
    {
        return !empty($this->google_access_token) && !empty($this->google_refresh_token);
    }

    /**
     * Check if user's Google token is expired.
     */
    public function isGoogleTokenExpired(): bool
    {
        if (!$this->google_token_expires_at) {
            return true;
        }

        return $this->google_token_expires_at->isPast();
    }

    /**
     * Check if Google Calendar sync is available for this user.
     * Returns true if company has it enabled AND user has connected their account.
     */
    public function canSyncGoogleCalendar(): bool
    {
        if (!$this->company) {
            return false;
        }

        $settings = $this->company->settings ?? [];
        $companyEnabled = $settings['gmail_sync_enabled'] ?? false;

        return $companyEnabled && $this->hasGoogleConnected();
    }

    /**
     * Check if company has Google Calendar sync enabled.
     */
    public function companyHasGoogleSyncEnabled(): bool
    {
        if (!$this->company) {
            return false;
        }

        $settings = $this->company->settings ?? [];

        // Check if both configured and enabled
        $isConfigured = !empty($settings['google_client_id']) && !empty($settings['google_client_secret']);
        $isEnabled = $settings['gmail_sync_enabled'] ?? false;

        return $isConfigured && $isEnabled;
    }

    /**
     * Disconnect Google account.
     */
    public function disconnectGoogle(): void
    {
        $this->update([
            'google_id' => null,
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_calendar_id' => null,
            'google_connected_at' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Client Portal Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get inbox workspaces where this user is added as a guest.
     */
    public function inboxGuestWorkspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_guests')
            ->where('workspaces.type', 'inbox')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    /**
     * Check if user is an inbox client (guest with access to at least one inbox workspace).
     */
    public function isInboxClient(): bool
    {
        return $this->is_guest && $this->inboxGuestWorkspaces()->exists();
    }

    /**
     * Get all tickets (tasks) for this inbox client.
     * Returns tasks where:
     * - User created the task, OR
     * - Task's source_email matches user's email
     */
    public function inboxTickets()
    {
        $workspaceIds = $this->inboxGuestWorkspaces()->pluck('workspaces.id');

        return \App\Modules\Task\Models\Task::whereIn('workspace_id', $workspaceIds)
            ->where(function ($query) {
                $query->where('created_by', $this->id)
                    ->orWhere('source_email', $this->email);
            });
    }
}

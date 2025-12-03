<?php

namespace App\Models;

use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class ClientCrm extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'client_crm';

    /**
     * Client types.
     */
    public const TYPE_EXTERNAL_CONSULTANT = 'external_consultant';
    public const TYPE_CLIENT = 'client';

    public const TYPES = [
        self::TYPE_EXTERNAL_CONSULTANT => [
            'label' => 'External Consultant',
            'color' => 'info',
        ],
        self::TYPE_CLIENT => [
            'label' => 'Client',
            'color' => 'success',
        ],
    ];

    /**
     * Client statuses.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_INVITED = 'invited';

    public const STATUSES = [
        self::STATUS_ACTIVE => [
            'label' => 'Active',
            'color' => 'success',
        ],
        self::STATUS_INACTIVE => [
            'label' => 'Inactive',
            'color' => 'neutral',
        ],
        self::STATUS_INVITED => [
            'label' => 'Invited',
            'color' => 'warning',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'company_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'type',
        'client_portal_access',
        'tags',
        'status',
        'invitation_token',
        'invitation_expires_at',
        'invited_at',
        'accepted_at',
        'last_login_at',
        'last_login_ip',
        'timezone',
        'avatar_path',
        'notes',
        'phone',
        'company_name',
        'position',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'invitation_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'client_portal_access' => 'boolean',
            'tags' => 'array',
            'invitation_expires_at' => 'datetime',
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if invitation is pending.
     */
    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    /**
     * Check if invitation has expired.
     */
    public function isInvitationExpired(): bool
    {
        return $this->invitation_expires_at && $this->invitation_expires_at->isPast();
    }

    /**
     * Bootstrap the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ClientCrm $client) {
            if (empty($client->uuid)) {
                $client->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the company that the client belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this client.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the workspaces assigned to this client.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'client_crm_workspace')
            ->withTimestamps();
    }

    /**
     * Get the client's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the client's initials.
     */
    public function getInitialsAttribute(): string
    {
        $first = substr($this->first_name ?? '', 0, 1);
        $last = substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last) ?: 'C';
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get the type color.
     */
    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'neutral';
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'neutral';
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path) {
            return \Storage::url($this->avatar_path);
        }
        return null;
    }

    /**
     * Check if client is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if client has portal access.
     */
    public function hasPortalAccess(): bool
    {
        return $this->client_portal_access;
    }
}

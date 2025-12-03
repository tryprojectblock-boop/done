<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use App\Modules\Core\Support\BaseModel;
use App\Modules\Workspace\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkspaceInvitation extends BaseModel
{
    protected $table = 'workspace_invitations';

    protected $fillable = [
        'workspace_id',
        'email',
        'role',
        'token',
        'invited_by',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => WorkspaceRole::class,
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WorkspaceInvitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }

            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
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

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', strtolower($email));
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function accept(User $user): void
    {
        $this->workspace->addMember($user, $this->role, $this->inviter);

        $this->update(['accepted_at' => now()]);
    }

    public function getAcceptUrl(): string
    {
        return route('workspace.invitation.accept', [
            'token' => $this->token,
        ]);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }
}

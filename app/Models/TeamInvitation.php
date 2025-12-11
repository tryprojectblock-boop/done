<?php

namespace App\Models;

use App\Modules\Auth\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    protected $table = 'team_invitations';

    protected $fillable = [
        'company_id',
        'user_id',
        'invited_by',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'rejected_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TeamInvitation $invitation) {
            if (empty($invitation->uuid)) {
                $invitation->uuid = (string) Str::uuid();
            }

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return is_null($this->accepted_at)
            && is_null($this->rejected_at)
            && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public function isRejected(): bool
    {
        return !is_null($this->rejected_at);
    }

    public function accept(): void
    {
        // Get fresh user instance to ensure we're updating the actual record
        $user = User::find($this->user_id);

        if ($user) {
            // Check if user already has a membership in this company
            $existingMembership = \DB::table('company_user')
                ->where('company_id', $this->company_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$existingMembership) {
                // Add user to the company via pivot table with the invited role
                \DB::table('company_user')->insert([
                    'company_id' => $this->company_id,
                    'user_id' => $user->id,
                    'role' => $this->role,
                    'is_primary' => false, // This is not their primary company
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ensure user status is active
            if ($user->status !== User::STATUS_ACTIVE) {
                $user->update(['status' => User::STATUS_ACTIVE]);
            }
        }

        $this->update(['accepted_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['rejected_at' => now()]);
    }

    public function getAcceptUrl(): string
    {
        return route('team.invitation.show', ['token' => $this->token]);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }

    public static function hasPendingInvitation(int $companyId, int $userId): bool
    {
        return static::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->pending()
            ->exists();
    }
}

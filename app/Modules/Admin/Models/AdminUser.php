<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Enums\AdminRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class AdminUser extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_users';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'verification_code',
        'verification_code_expires_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected function casts(): array
    {
        return [
            'role' => AdminRole::class,
            'is_active' => 'boolean',
            'verification_code_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AdminUser $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function isAdministrator(): bool
    {
        return $this->role === AdminRole::ADMINISTRATOR;
    }

    public function isMember(): bool
    {
        return $this->role === AdminRole::MEMBER;
    }

    public function canManageAdmins(): bool
    {
        return $this->role->canManageAdmins();
    }

    public function canManageSettings(): bool
    {
        return $this->role->canManageSettings();
    }

    public function generateVerificationCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    public function verifyCode(string $code): bool
    {
        if ($this->verification_code !== $code) {
            return false;
        }

        if ($this->verification_code_expires_at && $this->verification_code_expires_at->isPast()) {
            return false;
        }

        // Clear the code after successful verification
        $this->update([
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        return true;
    }

    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function getAvatarUrlAttribute(): string
    {
        $name = urlencode($this->name ?? $this->email ?? 'Admin');
        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=ffffff&size=128&bold=true";
    }
}

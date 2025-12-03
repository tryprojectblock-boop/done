<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Modules\Auth\Enums\RegistrationStep;
use App\Modules\Core\Support\BaseModel;
use App\Modules\Core\Traits\HasUuid;
use Illuminate\Support\Str;

class PendingRegistration extends BaseModel
{
    use HasUuid;

    protected $table = 'pending_registrations';

    protected $fillable = [
        'uuid',
        'email',
        'activation_code',
        'activation_code_expires_at',
        'email_verified_at',
        'first_name',
        'last_name',
        'password',
        'company_name',
        'company_size',
        'website_protocol',
        'website_url',
        'industry_type',
        'invited_emails',
        'registration_step',
        'ip_address',
        'user_agent',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'activation_code_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'invited_emails' => 'array',
            'registration_step' => RegistrationStep::class,
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $registration) {
            if (empty($registration->activation_code)) {
                $registration->activation_code = self::generateActivationCode();
            }
            if (empty($registration->activation_code_expires_at)) {
                $registration->activation_code_expires_at = now()->addHours(72);
            }
            if (empty($registration->registration_step)) {
                $registration->registration_step = RegistrationStep::EMAIL_SUBMITTED;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('activation_code_expires_at', '<', now())
            ->whereNull('email_verified_at');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
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

    public static function generateActivationCode(): string
    {
        return strtoupper(Str::random(6));
    }

    public function isActivationCodeValid(): bool
    {
        return $this->activation_code_expires_at->isFuture();
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function verifyEmail(): void
    {
        $this->update([
            'email_verified_at' => now(),
            'registration_step' => RegistrationStep::EMAIL_VERIFIED,
        ]);
    }

    public function regenerateActivationCode(): void
    {
        $this->update([
            'activation_code' => self::generateActivationCode(),
            'activation_code_expires_at' => now()->addHours(72),
        ]);
    }

    public function completeProfileStep(array $data): void
    {
        $this->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'password' => $data['password'],
            'registration_step' => RegistrationStep::PROFILE_COMPLETED,
        ]);
    }

    public function completeCompanyStep(array $data): void
    {
        $this->update([
            'company_name' => $data['company_name'],
            'company_size' => $data['company_size'],
            'website_protocol' => $data['website_protocol'] ?? 'https',
            'website_url' => $data['website_url'] ?? null,
            'industry_type' => $data['industry_type'],
            'registration_step' => RegistrationStep::COMPANY_COMPLETED,
        ]);
    }

    public function completeInvitationsStep(array $emails): void
    {
        $this->update([
            'invited_emails' => array_filter($emails),
            'registration_step' => RegistrationStep::INVITATIONS_SENT,
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'registration_step' => RegistrationStep::COMPLETED,
        ]);
    }

    public function getFullWebsiteUrl(): ?string
    {
        if (! $this->website_url) {
            return null;
        }

        return ($this->website_protocol ?? 'https') . '://' . $this->website_url;
    }

    public static function findByEmail(string $email): ?self
    {
        return self::forEmail($email)->pending()->first();
    }

    public static function findByActivationCode(string $email, string $code): ?self
    {
        return self::forEmail($email)
            ->where('activation_code', strtoupper($code))
            ->pending()
            ->first();
    }
}

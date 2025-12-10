<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Models\User;
use App\Modules\Admin\Models\Plan;
use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use App\Modules\Core\Support\BaseModel;
use App\Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    use HasUuid;

    protected $table = 'companies';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'size',
        'industry_type',
        'website_url',
        'logo_path',
        'owner_id',
        'plan_id',
        'billing_cycle',
        'subscription_starts_at',
        'subscription_ends_at',
        'applied_coupon_code',
        'discount_percent',
        'settings',
        'trial_ends_at',
        'paused_at',
        'pause_reason',
        'pause_description',
        'paused_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => CompanySize::class,
            'industry_type' => IndustryType::class,
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'subscription_starts_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'paused_at' => 'datetime',
            'discount_percent' => 'decimal:2',
        ];
    }

    /**
     * Check if the company account is paused
     */
    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    protected static function booted(): void
    {
        static::creating(function (self $company) {
            if (empty($company->slug)) {
                $company->slug = self::generateUniqueSlug($company->name);
            }
            if (empty($company->trial_ends_at)) {
                $company->trial_ends_at = now()->addDays(14); // 14-day trial
            }
        });
    }

    /**
     * Generate a unique slug for the company.
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = str($name)->slug()->toString();
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }

        return (int) max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function getLogoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return file_upload()->getUrl($this->logo_path);
    }

    /**
     * Check if the company has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->plan_id) {
            return false;
        }

        // If no end date, subscription is active (lifetime or free plan)
        if (!$this->subscription_ends_at) {
            return true;
        }

        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if the subscription is expired
     */
    public function isSubscriptionExpired(): bool
    {
        if (!$this->subscription_ends_at) {
            return false;
        }

        return $this->subscription_ends_at->isPast();
    }

    /**
     * Get subscription days remaining
     */
    public function subscriptionDaysRemaining(): int
    {
        if (!$this->subscription_ends_at) {
            return 0;
        }

        return (int) max(0, now()->diffInDays($this->subscription_ends_at, false));
    }

    /**
     * Get the billing cycle label
     */
    public function getBillingCycleLabel(): string
    {
        return match($this->billing_cycle) {
            '1_month' => 'Monthly',
            '3_month' => 'Quarterly',
            '6_month' => 'Semi-Annual',
            '12_month' => 'Annual',
            '3_year' => '3 Years',
            '5_year' => '5 Years',
            default => 'N/A',
        };
    }

    /**
     * Get the current plan price based on billing cycle
     */
    public function getCurrentPlanPrice(): float
    {
        if (!$this->plan) {
            return 0;
        }

        $priceField = 'price_' . str_replace('_', '_', $this->billing_cycle);
        return (float) ($this->plan->{$priceField} ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Plan Limit Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the workspace limit for this company's plan
     */
    public function getWorkspaceLimit(): int
    {
        return $this->plan?->workspace_limit ?? 1;
    }

    /**
     * Get the team member limit for this company's plan
     */
    public function getTeamMemberLimit(): int
    {
        return $this->plan?->team_member_limit ?? 5;
    }

    /**
     * Get the storage limit in GB for this company's plan
     */
    public function getStorageLimitGb(): int
    {
        return $this->plan?->storage_limit_gb ?? 5;
    }

    /**
     * Check if the company has unlimited workspaces
     */
    public function hasUnlimitedWorkspaces(): bool
    {
        return $this->plan && $this->plan->workspace_limit === 0;
    }

    /**
     * Check if the company has unlimited team members
     */
    public function hasUnlimitedTeamMembers(): bool
    {
        return $this->plan && $this->plan->team_member_limit === 0;
    }

    /**
     * Check if the company has unlimited storage
     */
    public function hasUnlimitedStorage(): bool
    {
        return $this->plan && $this->plan->storage_limit_gb === 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Module Feature Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the milestones module is enabled for this company
     */
    public function isMilestonesEnabled(): bool
    {
        $settings = $this->settings ?? [];
        return $settings['milestones_enabled'] ?? true; // Enabled by default
    }

    /**
     * Check if a specific module is enabled
     */
    public function isModuleEnabled(string $module): bool
    {
        $settings = $this->settings ?? [];
        return $settings["{$module}_enabled"] ?? true;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Models\User;
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
        'settings',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => CompanySize::class,
            'industry_type' => IndustryType::class,
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
        ];
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
}

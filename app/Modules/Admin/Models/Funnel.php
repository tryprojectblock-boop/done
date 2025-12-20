<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Funnel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'trigger_tag_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function triggerTag(): BelongsTo
    {
        return $this->belongsTo(FunnelTag::class, 'trigger_tag_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FunnelStep::class)->orderBy('step_order');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(FunnelSubscriber::class);
    }

    public function activeSubscribers(): HasMany
    {
        return $this->hasMany(FunnelSubscriber::class)->where('status', 'active');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(FunnelEmailLog::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'funnel_subscribers')
            ->withPivot(['current_step', 'subscribed_at', 'completed_at', 'status'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStepCount(): int
    {
        return $this->steps()->count();
    }

    public function getSubscriberCount(): int
    {
        return $this->subscribers()->count();
    }

    public function getActiveSubscriberCount(): int
    {
        return $this->activeSubscribers()->count();
    }
}

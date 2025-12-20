<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FunnelStep extends Model
{
    protected $fillable = [
        'uuid',
        'funnel_id',
        'step_order',
        'name',
        'delay_days',
        'delay_hours',
        'condition_tag_id',
        'condition_type',
        'from_email',
        'from_name',
        'subject',
        'body_html',
        'body_text',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'delay_days' => 'integer',
            'delay_hours' => 'integer',
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

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function conditionTag(): BelongsTo
    {
        return $this->belongsTo(FunnelTag::class, 'condition_tag_id');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(FunnelEmailLog::class);
    }

    public function getTotalDelayInHours(): int
    {
        return ($this->delay_days * 24) + $this->delay_hours;
    }

    public function getDelayDisplayAttribute(): string
    {
        if ($this->delay_days === 0 && $this->delay_hours === 0) {
            return 'Immediately';
        }

        $parts = [];
        if ($this->delay_days > 0) {
            $parts[] = $this->delay_days . ' ' . Str::plural('day', $this->delay_days);
        }
        if ($this->delay_hours > 0) {
            $parts[] = $this->delay_hours . ' ' . Str::plural('hour', $this->delay_hours);
        }

        return implode(' ', $parts);
    }

    public function hasCondition(): bool
    {
        return $this->condition_type !== 'none' && $this->condition_tag_id !== null;
    }

    public function getConditionDisplayAttribute(): ?string
    {
        if (!$this->hasCondition()) {
            return null;
        }

        $tagName = $this->conditionTag?->display_name ?? $this->conditionTag?->name ?? 'Unknown';

        return match ($this->condition_type) {
            'has_tag' => "If user has tag: {$tagName}",
            'missing_tag' => "If user missing tag: {$tagName}",
            default => null,
        };
    }
}

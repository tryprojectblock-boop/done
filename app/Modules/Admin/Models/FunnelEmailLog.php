<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FunnelEmailLog extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'funnel_id',
        'funnel_step_id',
        'to_email',
        'subject',
        'status',
        'sent_at',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
        'clicked_links',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'open_count' => 'integer',
            'click_count' => 'integer',
            'clicked_links' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FunnelStep::class, 'funnel_step_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function wasOpened(): bool
    {
        return $this->opened_at !== null;
    }

    public function wasClicked(): bool
    {
        return $this->clicked_at !== null;
    }

    public function markSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function recordOpen(): void
    {
        $this->increment('open_count');
        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function recordClick(string $url): void
    {
        $this->increment('click_count');
        if (!$this->clicked_at) {
            $this->update(['clicked_at' => now()]);
        }

        $clickedLinks = $this->clicked_links ?? [];
        $clickedLinks[] = [
            'url' => $url,
            'at' => now()->toIso8601String(),
        ];
        $this->update(['clicked_links' => $clickedLinks]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'sent' => $this->wasOpened() ? ($this->wasClicked() ? 'secondary' : 'success') : 'info',
            'failed' => 'error',
            'bounced' => 'error',
            default => 'ghost',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'sent') {
            if ($this->wasClicked()) {
                return 'Clicked';
            }
            if ($this->wasOpened()) {
                return 'Opened';
            }
            return 'Sent';
        }

        return ucfirst($this->status);
    }
}

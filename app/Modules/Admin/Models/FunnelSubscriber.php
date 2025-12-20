<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelSubscriber extends Model
{
    protected $fillable = [
        'user_id',
        'funnel_id',
        'current_step',
        'subscribed_at',
        'completed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'subscribed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(FunnelEmailLog::class, 'user_id', 'user_id')
            ->where('funnel_id', $this->funnel_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        $this->update(['status' => 'active']);
    }

    public function unsubscribe(): void
    {
        $this->update(['status' => 'unsubscribed']);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // Notification types
    public const TYPE_MENTION = 'mention';
    public const TYPE_TASK_ASSIGNED = 'task_assigned';
    public const TYPE_TASK_COMMENT = 'task_comment';
    public const TYPE_TASK_STATUS = 'task_status';
    public const TYPE_CHANNEL_MEMBER_ADDED = 'channel_member_added';
    public const TYPE_CHANNEL_REPLY_MENTION = 'channel_reply_mention';
    public const TYPE_CHANNEL_JOIN_REQUEST = 'channel_join_request';
    public const TYPE_CHANNEL_JOIN_REJECTED = 'channel_join_rejected';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MENTION => 'icon-[tabler--at]',
            self::TYPE_TASK_ASSIGNED => 'icon-[tabler--user-plus]',
            self::TYPE_TASK_COMMENT => 'icon-[tabler--message]',
            self::TYPE_TASK_STATUS => 'icon-[tabler--check]',
            self::TYPE_CHANNEL_MEMBER_ADDED => 'icon-[tabler--users-plus]',
            self::TYPE_CHANNEL_REPLY_MENTION => 'icon-[tabler--at]',
            self::TYPE_CHANNEL_JOIN_REQUEST => 'icon-[tabler--user-question]',
            self::TYPE_CHANNEL_JOIN_REJECTED => 'icon-[tabler--user-x]',
            default => 'icon-[tabler--bell]',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MENTION => 'text-primary',
            self::TYPE_TASK_ASSIGNED => 'text-success',
            self::TYPE_TASK_COMMENT => 'text-info',
            self::TYPE_TASK_STATUS => 'text-warning',
            self::TYPE_CHANNEL_MEMBER_ADDED => 'text-success',
            self::TYPE_CHANNEL_REPLY_MENTION => 'text-primary',
            self::TYPE_CHANNEL_JOIN_REQUEST => 'text-info',
            self::TYPE_CHANNEL_JOIN_REJECTED => 'text-error',
            default => 'text-base-content',
        };
    }
}

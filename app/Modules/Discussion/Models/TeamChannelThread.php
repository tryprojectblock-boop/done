<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TeamChannelThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'channel_id',
        'company_id',
        'created_by',
        'title',
        'content',
        'replies_count',
        'last_reply_at',
        'is_pinned',
    ];

    protected $casts = [
        'replies_count' => 'integer',
        'last_reply_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamChannelThread $thread) {
            if (empty($thread->uuid)) {
                $thread->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TeamChannel::class, 'channel_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TeamChannelReply::class, 'thread_id')->whereNull('parent_id');
    }

    public function allReplies(): HasMany
    {
        return $this->hasMany(TeamChannelReply::class, 'thread_id');
    }

    // ==================== PERMISSIONS ====================

    public function canView(User $user): bool
    {
        return $this->channel->canView($user);
    }

    /**
     * Only the thread creator can edit their own thread.
     */
    public function canEdit(User $user): bool
    {
        return $user->id === $this->created_by;
    }

    /**
     * Thread creator can delete their own thread.
     * Admin/Owner can delete any thread (but not edit).
     */
    public function canDelete(User $user): bool
    {
        return $user->id === $this->created_by || $user->isAdminOrHigher();
    }

    public function canReply(User $user): bool
    {
        return $this->channel->canPost($user);
    }

    public function canPin(User $user): bool
    {
        return $this->channel->canManage($user);
    }

    // ==================== COUNTERS ====================

    public function updateRepliesCount(): void
    {
        $this->update(['replies_count' => $this->allReplies()->count()]);
    }

    public function updateLastReply(): void
    {
        $lastReply = $this->allReplies()->latest()->first();
        $this->update(['last_reply_at' => $lastReply?->created_at ?? $this->created_at]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TeamChannelReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'thread_id',
        'user_id',
        'parent_id',
        'content',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamChannelReply $reply) {
            if (empty($reply->uuid)) {
                $reply->uuid = (string) Str::uuid();
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

    public function thread(): BelongsTo
    {
        return $this->belongsTo(TeamChannelThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TeamChannelReply::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TeamChannelReply::class, 'parent_id');
    }

    // ==================== PERMISSIONS ====================

    /**
     * Only the reply author can edit their own reply.
     */
    public function canEdit(User $user): bool
    {
        return $user->id === $this->user_id;
    }

    /**
     * Reply author can delete their own reply.
     * Admin/Owner can delete any reply (but not edit).
     */
    public function canDelete(User $user): bool
    {
        return $user->id === $this->user_id || $user->isAdminOrHigher();
    }

    // ==================== HELPERS ====================

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

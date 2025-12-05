<?php

declare(strict_types=1);

namespace App\Modules\Idea\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class IdeaComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'idea_id',
        'user_id',
        'content',
        'parent_id',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (IdeaComment $comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = (string) Str::uuid();
            }
        });

        static::created(function (IdeaComment $comment) {
            $comment->idea->updateCommentsCount();
        });

        static::deleted(function (IdeaComment $comment) {
            $comment->idea->updateCommentsCount();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(IdeaComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(IdeaComment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    // ==================== HELPER METHODS ====================

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canEdit(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

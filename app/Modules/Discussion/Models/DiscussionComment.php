<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
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

    // ==================== RELATIONSHIPS ====================

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionCommentAttachment::class, 'comment_id');
    }

    // ==================== HELPER METHODS ====================

    public function canEdit(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdminOrHigher();
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

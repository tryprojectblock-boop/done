<?php

declare(strict_types=1);

namespace App\Modules\Document\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentCommentReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'comment_id',
        'user_id',
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DocumentCommentReply $reply) {
            if (empty($reply->uuid)) {
                $reply->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function comment(): BelongsTo
    {
        return $this->belongsTo(DocumentComment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== HELPER METHODS ====================

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    // ==================== PERMISSION METHODS ====================

    public function canEdit(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        if ($this->isOwner($user) || $user->isAdminOrHigher()) {
            return true;
        }

        // Document creator can also delete replies
        return $this->comment->document->isCreator($user);
    }

    // ==================== STATE MANAGEMENT ====================

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

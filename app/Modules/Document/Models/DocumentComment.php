<?php

declare(strict_types=1);

namespace App\Modules\Document\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'document_id',
        'user_id',
        'selection_start',
        'selection_end',
        'selection_text',
        'selection_id',
        'content',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'selection_start' => 'integer',
            'selection_end' => 'integer',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DocumentComment $comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = (string) Str::uuid();
            }
            if (empty($comment->selection_id) && $comment->selection_start !== null) {
                $comment->selection_id = 'comment-' . Str::random(12);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DocumentCommentReply::class, 'comment_id')
            ->orderBy('created_at', 'asc');
    }

    // ==================== SCOPES ====================

    public function scopeForDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeWithSelection($query)
    {
        return $query->whereNotNull('selection_start');
    }

    // ==================== HELPER METHODS ====================

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasSelection(): bool
    {
        return $this->selection_start !== null && $this->selection_end !== null;
    }

    // ==================== PERMISSION METHODS ====================

    public function canEdit(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isOwner($user) || $user->isAdminOrHigher() || $this->document->isCreator($user);
    }

    public function canResolve(User $user): bool
    {
        // Owner, document creator, editors, or admins can resolve
        if ($this->isOwner($user) || $user->isAdminOrHigher()) {
            return true;
        }

        return $this->document->canEdit($user);
    }

    // ==================== STATE MANAGEMENT ====================

    public function markAsResolved(User $user): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);
    }

    public function markAsUnresolved(): void
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

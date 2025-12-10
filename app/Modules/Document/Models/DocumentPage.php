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

class DocumentPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'document_id',
        'title',
        'content',
        'sort_order',
        'created_by',
        'last_edited_by',
        'last_edited_at',
    ];

    protected function casts(): array
    {
        return [
            'last_edited_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DocumentPage $page) {
            if (empty($page->uuid)) {
                $page->uuid = (string) Str::uuid();
            }

            // Auto-set sort order if not provided
            if ($page->sort_order === null || $page->sort_order === 0) {
                $maxOrder = static::where('document_id', $page->document_id)->max('sort_order') ?? 0;
                $page->sort_order = $maxOrder + 1;
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class)
            ->orderBy('selection_start')
            ->orderBy('created_at');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)
            ->orderBy('version_number', 'desc');
    }

    // ==================== HELPER METHODS ====================

    public function updateLastEdited(User $user): void
    {
        $this->update([
            'last_edited_by' => $user->id,
            'last_edited_at' => now(),
        ]);
    }

    public function isFirstPage(): bool
    {
        return $this->sort_order === 1;
    }

    public function isLastPage(): bool
    {
        $maxOrder = static::where('document_id', $this->document_id)->max('sort_order');
        return $this->sort_order === $maxOrder;
    }
}

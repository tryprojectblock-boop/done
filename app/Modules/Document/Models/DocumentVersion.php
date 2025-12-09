<?php

declare(strict_types=1);

namespace App\Modules\Document\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'content',
        'version_number',
        'change_summary',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
        ];
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

    // ==================== SCOPES ====================

    public function scopeForDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    // ==================== HELPER METHODS ====================

    public function getFormattedDate(): string
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    public function getRelativeDate(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getChangeSummaryOrDefault(): string
    {
        return $this->change_summary ?? 'Document updated';
    }

    // ==================== FACTORY METHODS ====================

    public static function createForDocument(Document $document, User $user, ?string $summary = null): self
    {
        $nextVersion = $document->versions()->max('version_number') + 1;

        $version = self::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'content' => $document->content ?? '',
            'version_number' => $nextVersion,
            'change_summary' => $summary,
        ]);

        $document->update(['version_count' => $nextVersion]);

        return $version;
    }
}

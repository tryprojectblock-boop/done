<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DiscussionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ==================== ACCESSORS ====================

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    public function getIconAttribute(): string
    {
        $extension = pathinfo($this->original_filename, PATHINFO_EXTENSION);

        return match (strtolower($extension)) {
            'pdf' => 'tabler--file-type-pdf',
            'doc', 'docx' => 'tabler--file-type-doc',
            'xls', 'xlsx' => 'tabler--file-type-xls',
            'ppt', 'pptx' => 'tabler--file-type-ppt',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'tabler--photo',
            'zip', 'rar', '7z' => 'tabler--file-zip',
            'mp4', 'mov', 'avi' => 'tabler--video',
            'mp3', 'wav' => 'tabler--music',
            default => 'tabler--file',
        };
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskCommentAttachment extends Model
{
    use HasFactory;

    protected $table = 'task_comment_attachments';

    protected $fillable = [
        'comment_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (TaskCommentAttachment $attachment) {
            if ($attachment->file_path && Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'comment_id');
    }

    // ==================== HELPER METHODS ====================

    public function getUrl(): string
    {
        return Storage::disk('local')->url($this->file_path);
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}

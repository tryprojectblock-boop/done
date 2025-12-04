<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'task_id',
        'uploaded_by',
        'original_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'disk',
        'description',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TaskAttachment $attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
            // Set default disk if not provided
            if (empty($attachment->disk)) {
                $attachment->disk = config('filesystems.default_upload_disk', 'do_spaces');
            }
        });

        static::deleting(function (TaskAttachment $attachment) {
            // Delete the actual file when the attachment is deleted
            $disk = $attachment->disk ?? config('filesystems.default_upload_disk', 'do_spaces');
            if ($attachment->file_path && Storage::disk($disk)->exists($attachment->file_path)) {
                Storage::disk($disk)->delete($attachment->file_path);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the storage disk for this attachment
     */
    public function getStorageDisk(): string
    {
        return $this->disk ?? config('filesystems.default_upload_disk', 'do_spaces');
    }

    // ==================== RELATIONSHIPS ====================

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ==================== HELPER METHODS ====================

    public function getUrl(): string
    {
        $disk = $this->getStorageDisk();
        return Storage::disk($disk)->url($this->file_path);
    }

    public function getTemporaryUrl(int $expirationMinutes = 60): string
    {
        $disk = $this->getStorageDisk();
        return Storage::disk($disk)->temporaryUrl(
            $this->file_path,
            now()->addMinutes($expirationMinutes)
        );
    }

    public function getDownloadUrl(): string
    {
        return route('tasks.attachments.download', $this->uuid);
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

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIconClass(): string
    {
        return match (true) {
            $this->isImage() => 'icon-[tabler--photo]',
            $this->isPdf() => 'icon-[tabler--file-type-pdf]',
            str_contains($this->mime_type ?? '', 'word') => 'icon-[tabler--file-type-doc]',
            str_contains($this->mime_type ?? '', 'excel') || str_contains($this->mime_type ?? '', 'spreadsheet') => 'icon-[tabler--file-type-xls]',
            str_contains($this->mime_type ?? '', 'zip') || str_contains($this->mime_type ?? '', 'archive') => 'icon-[tabler--file-zip]',
            default => 'icon-[tabler--file]',
        };
    }
}

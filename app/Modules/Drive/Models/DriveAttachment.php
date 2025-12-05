<?php

declare(strict_types=1);

namespace App\Modules\Drive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'uploaded_by',
        'name',
        'description',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DriveAttachment $attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function (DriveAttachment $attachment) {
            // Delete file from storage when force deleting
            if ($attachment->isForceDeleting()) {
                Storage::disk('do_spaces')->delete($attachment->file_path);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            DriveAttachmentTag::class,
            'drive_attachment_tag',
            'drive_attachment_id',
            'drive_attachment_tag_id'
        );
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'drive_attachment_shares')
            ->withPivot('shared_by')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getUrlAttribute(): string
    {
        // Use CDN endpoint if available (from DO_SPACES_CDN_ENDPOINT)
        $cdnEndpoint = config('filesystems.disks.do_spaces.url');
        if ($cdnEndpoint) {
            return rtrim($cdnEndpoint, '/') . '/' . $this->file_path;
        }

        return Storage::disk('do_spaces')->url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getFileCategoryAttribute(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return 'images';
        }
        if (str_starts_with($this->mime_type, 'video/')) {
            return 'videos';
        }
        if (str_starts_with($this->mime_type, 'audio/')) {
            return 'audio';
        }
        if (in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
        ])) {
            return 'documents';
        }

        return 'other';
    }

    public function getIconAttribute(): string
    {
        $mimeType = $this->mime_type;

        if (str_starts_with($mimeType, 'image/')) {
            return 'tabler--photo';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'tabler--video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'tabler--music';
        }
        if ($mimeType === 'application/pdf') {
            return 'tabler--file-type-pdf';
        }
        if (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'tabler--file-type-doc';
        }
        if (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return 'tabler--file-type-xls';
        }
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar')) {
            return 'tabler--file-zip';
        }

        return 'tabler--file';
    }

    // ==================== SCOPES ====================

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Files uploaded by user
            $q->where('uploaded_by', $user->id)
                // Or files shared with user
                ->orWhereHas('sharedWith', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id);
                });
        });
    }

    // ==================== HELPER METHODS ====================

    public function isOwnedBy(User $user): bool
    {
        return $this->uploaded_by === $user->id;
    }

    public function isSharedWith(User $user): bool
    {
        return $this->sharedWith()->where('user_id', $user->id)->exists();
    }

    public function canView(User $user): bool
    {
        return $this->isOwnedBy($user) || $this->isSharedWith($user) || $user->isAdminOrHigher();
    }

    public function canEdit(User $user): bool
    {
        return $this->isOwnedBy($user) || $user->isAdminOrHigher();
    }

    public function canDelete(User $user): bool
    {
        return $this->isOwnedBy($user) || $user->isAdminOrHigher();
    }

    public function shareWith(User $user, User $sharedBy): void
    {
        if (!$this->isSharedWith($user) && $user->id !== $this->uploaded_by) {
            $this->sharedWith()->attach($user->id, ['shared_by' => $sharedBy->id]);
        }
    }

    public function unshareWith(User $user): void
    {
        $this->sharedWith()->detach($user->id);
    }
}

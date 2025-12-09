<?php

declare(strict_types=1);

namespace App\Modules\Document\Models;

use App\Models\User;
use App\Modules\Document\Enums\CollaboratorRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentCollaborator extends Model
{
    protected $table = 'document_collaborators';

    protected $fillable = [
        'document_id',
        'user_id',
        'role',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'role' => CollaboratorRole::class,
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

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ==================== HELPER METHODS ====================

    public function isEditor(): bool
    {
        return $this->role === CollaboratorRole::EDITOR;
    }

    public function isReader(): bool
    {
        return $this->role === CollaboratorRole::READER;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Document\Models;

use App\Models\User;
use App\Modules\Document\Enums\CollaboratorRole;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'workspace_id',
        'created_by',
        'title',
        'description',
        'content',
        'last_edited_by',
        'last_edited_at',
        'version_count',
    ];

    protected function casts(): array
    {
        return [
            'last_edited_at' => 'datetime',
            'version_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Document $document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'document_collaborators')
            ->withPivot(['role', 'invited_by'])
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class)
            ->orderBy('selection_start')
            ->orderBy('created_at');
    }

    public function unresolvedComments(): HasMany
    {
        return $this->hasMany(DocumentComment::class)
            ->where('is_resolved', false)
            ->orderBy('selection_start')
            ->orderBy('created_at');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)
            ->orderBy('version_number', 'desc');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(DocumentPage::class)
            ->orderBy('sort_order');
    }

    // ==================== SCOPES ====================

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // User is creator of a document in their company
            $q->where(function ($sub) use ($user) {
                $sub->where('created_by', $user->id)
                    ->where('company_id', $user->company_id);
            })
            // Or user is a collaborator (on any document they've been shared on)
            ->orWhereHas('collaborators', function ($sub) use ($user) {
                $sub->where('users.id', $user->id);
            });
        });
    }

    public function scopeEditableBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // User is creator of a document in their company
            $q->where(function ($sub) use ($user) {
                $sub->where('created_by', $user->id)
                    ->where('company_id', $user->company_id);
            })
            // Or user is an editor collaborator (on any document they've been shared on)
            ->orWhereHas('collaborators', function ($sub) use ($user) {
                $sub->where('users.id', $user->id)
                    ->where('document_collaborators.role', CollaboratorRole::EDITOR->value);
            });
        });
    }

    // ==================== HELPER METHODS ====================

    public function isCreator(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isCollaborator(User $user): bool
    {
        return $this->collaborators()->where('user_id', $user->id)->exists();
    }

    public function getCollaboratorRole(User $user): ?CollaboratorRole
    {
        $collaborator = $this->collaborators()->where('user_id', $user->id)->first();

        if (!$collaborator) {
            return null;
        }

        return CollaboratorRole::tryFrom($collaborator->pivot->role);
    }

    public function isEditor(User $user): bool
    {
        if ($this->isCreator($user)) {
            return true;
        }

        return $this->getCollaboratorRole($user) === CollaboratorRole::EDITOR;
    }

    public function isReader(User $user): bool
    {
        return $this->getCollaboratorRole($user) === CollaboratorRole::READER;
    }

    // ==================== PERMISSION METHODS ====================

    public function canView(User $user): bool
    {
        // Admin/Owner can view all in their company
        if ($user->isAdminOrHigher() && $this->company_id === $user->company_id) {
            return true;
        }

        // Creator can always view
        if ($this->isCreator($user)) {
            return true;
        }

        // Collaborators can view
        return $this->isCollaborator($user);
    }

    public function canEdit(User $user): bool
    {
        // Admin/Owner can edit all in their company
        if ($user->isAdminOrHigher() && $this->company_id === $user->company_id) {
            return true;
        }

        // Creator can always edit
        if ($this->isCreator($user)) {
            return true;
        }

        // Only editor collaborators can edit
        return $this->isEditor($user);
    }

    public function canComment(User $user): bool
    {
        // Anyone who can view can comment (both editors and readers)
        return $this->canView($user);
    }

    public function canDelete(User $user): bool
    {
        // Only creator or admin can delete
        return $this->isCreator($user) || ($user->isAdminOrHigher() && $this->company_id === $user->company_id);
    }

    public function canInvite(User $user): bool
    {
        // Creator, admin, or editors can invite
        if ($this->isCreator($user) || ($user->isAdminOrHigher() && $this->company_id === $user->company_id)) {
            return true;
        }

        return $this->isEditor($user);
    }

    public function canViewVersions(User $user): bool
    {
        return $this->canView($user);
    }

    public function canRestoreVersion(User $user): bool
    {
        return $this->canEdit($user);
    }

    // ==================== COLLABORATOR MANAGEMENT ====================

    public function addCollaborator(User $user, CollaboratorRole $role, ?User $invitedBy = null): void
    {
        if (!$this->isCollaborator($user)) {
            $this->collaborators()->attach($user->id, [
                'role' => $role->value,
                'invited_by' => $invitedBy?->id,
            ]);
        }
    }

    public function updateCollaboratorRole(User $user, CollaboratorRole $role): void
    {
        $this->collaborators()->updateExistingPivot($user->id, [
            'role' => $role->value,
        ]);
    }

    public function removeCollaborator(User $user): void
    {
        $this->collaborators()->detach($user->id);
    }

    // ==================== VERSION MANAGEMENT ====================

    public function incrementVersionCount(): void
    {
        $this->increment('version_count');
    }

    public function updateLastEdited(User $user): void
    {
        $this->update([
            'last_edited_by' => $user->id,
            'last_edited_at' => now(),
        ]);
    }

    public function getLatestVersion(): ?DocumentVersion
    {
        return $this->versions()->first();
    }
}

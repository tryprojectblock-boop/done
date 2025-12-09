<?php

declare(strict_types=1);

namespace App\Modules\Document\Contracts;

use App\Models\User;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Models\DocumentComment;
use App\Modules\Document\Models\DocumentCommentReply;
use App\Modules\Document\Models\DocumentVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentServiceInterface
{
    // ==================== DOCUMENT CRUD ====================

    /**
     * Get documents accessible by a user with optional filters.
     */
    public function getDocumentsForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Get a document by UUID.
     */
    public function getDocumentByUuid(string $uuid): ?Document;

    /**
     * Create a new document.
     */
    public function createDocument(array $data, User $user): Document;

    /**
     * Update document metadata (title, description, workspace).
     */
    public function updateDocument(Document $document, array $data, User $user): Document;

    /**
     * Delete a document.
     */
    public function deleteDocument(Document $document, User $user): bool;

    // ==================== CONTENT MANAGEMENT ====================

    /**
     * Update document content and create a version.
     */
    public function updateContent(Document $document, string $content, User $user, ?string $summary = null): Document;

    /**
     * Auto-save document content (may or may not create version based on interval).
     */
    public function autoSave(Document $document, string $content, User $user): Document;

    // ==================== COLLABORATOR MANAGEMENT ====================

    /**
     * Add a collaborator to the document.
     */
    public function addCollaborator(Document $document, int $userId, string $role, User $invitedBy): void;

    /**
     * Remove a collaborator from the document.
     */
    public function removeCollaborator(Document $document, int $userId): void;

    /**
     * Update a collaborator's role.
     */
    public function updateCollaboratorRole(Document $document, int $userId, string $role): void;

    // ==================== COMMENTS ====================

    /**
     * Add a comment to the document.
     */
    public function addComment(Document $document, array $data, User $user): DocumentComment;

    /**
     * Update a comment.
     */
    public function updateComment(DocumentComment $comment, string $content, User $user): DocumentComment;

    /**
     * Delete a comment.
     */
    public function deleteComment(DocumentComment $comment, User $user): bool;

    /**
     * Resolve a comment.
     */
    public function resolveComment(DocumentComment $comment, User $user): DocumentComment;

    /**
     * Unresolve a comment.
     */
    public function unresolveComment(DocumentComment $comment, User $user): DocumentComment;

    // ==================== COMMENT REPLIES ====================

    /**
     * Add a reply to a comment.
     */
    public function addReply(DocumentComment $comment, string $content, User $user): DocumentCommentReply;

    /**
     * Update a reply.
     */
    public function updateReply(DocumentCommentReply $reply, string $content, User $user): DocumentCommentReply;

    /**
     * Delete a reply.
     */
    public function deleteReply(DocumentCommentReply $reply, User $user): bool;

    // ==================== VERSION HISTORY ====================

    /**
     * Get versions for a document.
     */
    public function getVersions(Document $document, int $limit = 50): Collection;

    /**
     * Get a specific version by version number.
     */
    public function getVersion(Document $document, int $versionNumber): ?DocumentVersion;

    /**
     * Get a specific version by ID.
     */
    public function getVersionById(Document $document, int $versionId): ?DocumentVersion;

    /**
     * Restore a document to a previous version.
     */
    public function restoreVersion(Document $document, int $versionNumber, User $user): Document;
}

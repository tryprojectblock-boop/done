<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Contracts;

use App\Models\User;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DiscussionServiceInterface
{
    public function getDiscussionsForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function getDiscussionByUuid(string $uuid): ?Discussion;

    public function createDiscussion(array $data, User $user): Discussion;

    public function updateDiscussion(Discussion $discussion, array $data, User $user): Discussion;

    public function deleteDiscussion(Discussion $discussion, User $user): bool;

    public function addComment(Discussion $discussion, string $content, User $user, ?int $parentId = null, array $attachments = []): DiscussionComment;

    public function updateComment(DiscussionComment $comment, string $content, User $user): DiscussionComment;

    public function deleteComment(DiscussionComment $comment, User $user): bool;

    public function addParticipant(Discussion $discussion, int $userId, User $invitedBy): void;

    public function removeParticipant(Discussion $discussion, int $userId): void;

    public function addAttachment(Discussion $discussion, array $fileData, User $user): void;

    public function removeAttachment(int $attachmentId): void;
}

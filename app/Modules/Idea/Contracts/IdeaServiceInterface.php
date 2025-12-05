<?php

declare(strict_types=1);

namespace App\Modules\Idea\Contracts;

use App\Models\User;
use App\Modules\Idea\Enums\IdeaStatus;
use App\Modules\Idea\Models\Idea;
use App\Modules\Idea\Models\IdeaComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IdeaServiceInterface
{
    public function getIdeasForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function getIdeasForWorkspace(int $workspaceId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function createIdea(array $data, User $user): Idea;

    public function updateIdea(Idea $idea, array $data, User $user): Idea;

    public function deleteIdea(Idea $idea, User $user): bool;

    public function changeStatus(Idea $idea, IdeaStatus $status, User $user): Idea;

    public function vote(Idea $idea, User $user, int $vote): void;

    public function removeVote(Idea $idea, User $user): void;

    public function addComment(Idea $idea, string $content, User $user, ?int $parentId = null): IdeaComment;

    public function updateComment(IdeaComment $comment, string $content, User $user): IdeaComment;

    public function deleteComment(IdeaComment $comment, User $user): bool;

    public function addMember(Idea $idea, int $userId, User $invitedBy): void;

    public function removeMember(Idea $idea, int $userId): void;

    public function getIdeaByUuid(string $uuid): ?Idea;
}

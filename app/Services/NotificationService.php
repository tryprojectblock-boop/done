<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;

class NotificationService
{
    /**
     * Create a mention notification.
     */
    public function createMentionNotification(
        User $mentionedUser,
        User $mentioner,
        Task $task,
        ?TaskComment $comment = null
    ): Notification {
        $context = $comment ? 'comment' : 'task description';

        return Notification::create([
            'user_id' => $mentionedUser->id,
            'type' => Notification::TYPE_MENTION,
            'title' => "{$mentioner->name} mentioned you",
            'message' => "You were mentioned in a {$context} on task: {$task->title}",
            'notifiable_type' => $comment ? TaskComment::class : Task::class,
            'notifiable_id' => $comment ? $comment->id : $task->id,
            'data' => [
                'mentioner_id' => $mentioner->id,
                'mentioner_name' => $mentioner->name,
                'mentioner_avatar' => $mentioner->avatar_url,
                'task_id' => $task->id,
                'task_uuid' => $task->uuid,
                'task_title' => $task->title,
                'task_url' => route('tasks.show', $task->uuid),
                'comment_id' => $comment?->id,
            ],
        ]);
    }

    /**
     * Create a task assigned notification.
     */
    public function createTaskAssignedNotification(
        User $assignee,
        User $assigner,
        Task $task
    ): Notification {
        return Notification::create([
            'user_id' => $assignee->id,
            'type' => Notification::TYPE_TASK_ASSIGNED,
            'title' => "Task assigned to you",
            'message' => "{$assigner->name} assigned you to: {$task->title}",
            'notifiable_type' => Task::class,
            'notifiable_id' => $task->id,
            'data' => [
                'assigner_id' => $assigner->id,
                'assigner_name' => $assigner->name,
                'assigner_avatar' => $assigner->avatar_url,
                'task_id' => $task->id,
                'task_uuid' => $task->uuid,
                'task_title' => $task->title,
                'task_url' => route('tasks.show', $task->uuid),
            ],
        ]);
    }

    /**
     * Create a task comment notification.
     */
    public function createTaskCommentNotification(
        User $recipient,
        User $commenter,
        Task $task,
        TaskComment $comment
    ): Notification {
        return Notification::create([
            'user_id' => $recipient->id,
            'type' => Notification::TYPE_TASK_COMMENT,
            'title' => "New comment on task",
            'message' => "{$commenter->name} commented on: {$task->title}",
            'notifiable_type' => TaskComment::class,
            'notifiable_id' => $comment->id,
            'data' => [
                'commenter_id' => $commenter->id,
                'commenter_name' => $commenter->name,
                'commenter_avatar' => $commenter->avatar_url,
                'task_id' => $task->id,
                'task_uuid' => $task->uuid,
                'task_title' => $task->title,
                'task_url' => route('tasks.show', $task->uuid),
                'comment_id' => $comment->id,
            ],
        ]);
    }

    /**
     * Parse mentions from HTML content and return mentioned user IDs.
     */
    public function parseMentionsFromContent(string $content): array
    {
        $userIds = [];

        // Match mention spans: <span class="mention" data-id="123">@name</span>
        if (preg_match_all('/data-id=["\'](\d+)["\']/', $content, $matches)) {
            $userIds = array_map('intval', $matches[1]);
        }

        return array_unique($userIds);
    }

    /**
     * Create notifications for all mentioned users in content.
     */
    public function notifyMentionedUsers(
        string $content,
        User $author,
        Task $task,
        ?TaskComment $comment = null
    ): array {
        $mentionedUserIds = $this->parseMentionsFromContent($content);
        $notifications = [];

        // Don't notify the author if they mention themselves
        $mentionedUserIds = array_filter($mentionedUserIds, fn($id) => $id !== $author->id);

        if (empty($mentionedUserIds)) {
            return [];
        }

        $users = User::whereIn('id', $mentionedUserIds)->get();

        foreach ($users as $user) {
            $notifications[] = $this->createMentionNotification($user, $author, $task, $comment);
        }

        return $notifications;
    }
}

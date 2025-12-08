<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionComment;
use App\Modules\Discussion\Models\TeamChannel;
use App\Modules\Discussion\Models\TeamChannelThread;
use App\Modules\Discussion\Models\TeamChannelReply;
use App\Modules\Idea\Models\Idea;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a mention notification for Task.
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
     * Create a mention notification for Idea.
     */
    public function createIdeaMentionNotification(
        User $mentionedUser,
        User $mentioner,
        Idea $idea
    ): Notification {
        return Notification::create([
            'user_id' => $mentionedUser->id,
            'type' => Notification::TYPE_MENTION,
            'title' => "{$mentioner->name} mentioned you",
            'message' => "You were mentioned in idea: {$idea->title}",
            'notifiable_type' => Idea::class,
            'notifiable_id' => $idea->id,
            'data' => [
                'mentioner_id' => $mentioner->id,
                'mentioner_name' => $mentioner->name,
                'mentioner_avatar' => $mentioner->avatar_url,
                'idea_id' => $idea->id,
                'idea_uuid' => $idea->uuid,
                'idea_title' => $idea->title,
                'task_url' => route('ideas.show', $idea->uuid),
            ],
        ]);
    }

    /**
     * Create a mention notification for Discussion.
     */
    public function createDiscussionMentionNotification(
        User $mentionedUser,
        User $mentioner,
        Discussion $discussion,
        ?DiscussionComment $comment = null
    ): Notification {
        $context = $comment ? 'comment' : 'discussion';

        return Notification::create([
            'user_id' => $mentionedUser->id,
            'type' => Notification::TYPE_MENTION,
            'title' => "{$mentioner->name} mentioned you",
            'message' => "You were mentioned in a {$context} on: {$discussion->title}",
            'notifiable_type' => $comment ? DiscussionComment::class : Discussion::class,
            'notifiable_id' => $comment ? $comment->id : $discussion->id,
            'data' => [
                'mentioner_id' => $mentioner->id,
                'mentioner_name' => $mentioner->name,
                'mentioner_avatar' => $mentioner->avatar_url,
                'discussion_id' => $discussion->id,
                'discussion_uuid' => $discussion->uuid,
                'discussion_title' => $discussion->title,
                'discussion_url' => route('discussions.show', $discussion->uuid),
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
     * Supports Task, Idea, and Discussion models.
     */
    public function notifyMentionedUsers(
        string $content,
        User $author,
        Model $entity,
        TaskComment|DiscussionComment|null $comment = null
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
            if ($entity instanceof Task) {
                $taskComment = $comment instanceof TaskComment ? $comment : null;
                $notifications[] = $this->createMentionNotification($user, $author, $entity, $taskComment);
            } elseif ($entity instanceof Idea) {
                $notifications[] = $this->createIdeaMentionNotification($user, $author, $entity);
            } elseif ($entity instanceof Discussion) {
                $discussionComment = $comment instanceof DiscussionComment ? $comment : null;
                $notifications[] = $this->createDiscussionMentionNotification($user, $author, $entity, $discussionComment);
            }
        }

        return $notifications;
    }

    /**
     * Create a notification when a user is added to a team channel.
     */
    public function createChannelMemberAddedNotification(
        User $member,
        User $inviter,
        TeamChannel $channel
    ): Notification {
        return Notification::create([
            'user_id' => $member->id,
            'type' => Notification::TYPE_CHANNEL_MEMBER_ADDED,
            'title' => "Added to channel",
            'message' => "{$inviter->name} added you to channel: {$channel->name}",
            'notifiable_type' => TeamChannel::class,
            'notifiable_id' => $channel->id,
            'data' => [
                'inviter_id' => $inviter->id,
                'inviter_name' => $inviter->name,
                'inviter_avatar' => $inviter->avatar_url,
                'channel_id' => $channel->id,
                'channel_uuid' => $channel->uuid,
                'channel_name' => $channel->name,
                'channel_tag' => $channel->tag,
                'channel_url' => route('channels.show', $channel->uuid),
            ],
        ]);
    }

    /**
     * Create a mention notification for a Team Channel reply.
     */
    public function createChannelReplyMentionNotification(
        User $mentionedUser,
        User $mentioner,
        TeamChannelThread $thread,
        TeamChannelReply $reply
    ): Notification {
        return Notification::create([
            'user_id' => $mentionedUser->id,
            'type' => Notification::TYPE_CHANNEL_REPLY_MENTION,
            'title' => "{$mentioner->name} mentioned you",
            'message' => "You were mentioned in a reply on: {$thread->title}",
            'notifiable_type' => TeamChannelReply::class,
            'notifiable_id' => $reply->id,
            'data' => [
                'mentioner_id' => $mentioner->id,
                'mentioner_name' => $mentioner->name,
                'mentioner_avatar' => $mentioner->avatar_url,
                'thread_id' => $thread->id,
                'thread_uuid' => $thread->uuid,
                'thread_title' => $thread->title,
                'channel_id' => $thread->channel_id,
                'channel_uuid' => $thread->channel->uuid,
                'channel_name' => $thread->channel->name,
                'thread_url' => route('channels.threads.show', [$thread->channel->uuid, $thread->uuid]),
                'reply_id' => $reply->id,
            ],
        ]);
    }

    /**
     * Create notifications for all mentioned users in Team Channel reply content.
     */
    public function notifyMentionedUsersInChannelReply(
        string $content,
        User $author,
        TeamChannelThread $thread,
        TeamChannelReply $reply
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
            $notifications[] = $this->createChannelReplyMentionNotification($user, $author, $thread, $reply);
        }

        return $notifications;
    }
}

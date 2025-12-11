<?php

declare(strict_types=1);

namespace App\Modules\Task\Enums;

enum ActivityType: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case STATUS_CHANGED = 'status_changed';
    case PRIORITY_CHANGED = 'priority_changed';
    case ASSIGNEE_CHANGED = 'assignee_changed';
    case TYPE_CHANGED = 'type_changed';
    case TITLE_CHANGED = 'title_changed';
    case DESCRIPTION_CHANGED = 'description_changed';
    case DUE_DATE_CHANGED = 'due_date_changed';
    case TAG_ADDED = 'tag_added';
    case TAG_REMOVED = 'tag_removed';
    case WATCHER_ADDED = 'watcher_added';
    case WATCHER_REMOVED = 'watcher_removed';
    case ATTACHMENT_ADDED = 'attachment_added';
    case ATTACHMENT_REMOVED = 'attachment_removed';
    case COMMENT_ADDED = 'comment_added';
    case COMMENT_EDITED = 'comment_edited';
    case COMMENT_DELETED = 'comment_deleted';
    case LINKED = 'linked';
    case UNLINKED = 'unlinked';
    case REOPENED = 'reopened';
    case CLOSED = 'closed';
    case PUT_ON_HOLD = 'put_on_hold';
    case RESUMED = 'resumed';
    case PARENT_CHANGED = 'parent_changed';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'created this task',
            self::UPDATED => 'updated this task',
            self::DELETED => 'deleted this task',
            self::STATUS_CHANGED => 'changed the status',
            self::PRIORITY_CHANGED => 'changed the priority',
            self::ASSIGNEE_CHANGED => 'changed the assignee',
            self::TYPE_CHANGED => 'changed the task type',
            self::TITLE_CHANGED => 'updated the title',
            self::DESCRIPTION_CHANGED => 'updated the description',
            self::DUE_DATE_CHANGED => 'changed the due date',
            self::TAG_ADDED => 'added a tag',
            self::TAG_REMOVED => 'removed a tag',
            self::WATCHER_ADDED => 'added a watcher',
            self::WATCHER_REMOVED => 'removed a watcher',
            self::ATTACHMENT_ADDED => 'added an attachment',
            self::ATTACHMENT_REMOVED => 'removed an attachment',
            self::COMMENT_ADDED => 'added a comment',
            self::COMMENT_EDITED => 'edited a comment',
            self::COMMENT_DELETED => 'deleted a comment',
            self::LINKED => 'linked a task',
            self::UNLINKED => 'unlinked a task',
            self::REOPENED => 'reopened this task',
            self::CLOSED => 'closed this task',
            self::PUT_ON_HOLD => 'put this task on hold',
            self::RESUMED => 'resumed this task',
            self::PARENT_CHANGED => 'changed the parent task',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CREATED => 'tabler--plus',
            self::UPDATED => 'tabler--edit',
            self::DELETED => 'tabler--trash',
            self::STATUS_CHANGED => 'tabler--arrow-right',
            self::PRIORITY_CHANGED => 'tabler--flag',
            self::ASSIGNEE_CHANGED => 'tabler--user',
            self::TYPE_CHANGED => 'tabler--category',
            self::TITLE_CHANGED => 'tabler--text-caption',
            self::DESCRIPTION_CHANGED => 'tabler--align-left',
            self::DUE_DATE_CHANGED => 'tabler--calendar',
            self::TAG_ADDED, self::TAG_REMOVED => 'tabler--tag',
            self::WATCHER_ADDED, self::WATCHER_REMOVED => 'tabler--eye',
            self::ATTACHMENT_ADDED, self::ATTACHMENT_REMOVED => 'tabler--paperclip',
            self::COMMENT_ADDED, self::COMMENT_EDITED, self::COMMENT_DELETED => 'tabler--message',
            self::LINKED, self::UNLINKED => 'tabler--link',
            self::REOPENED => 'tabler--refresh',
            self::CLOSED => 'tabler--check',
            self::PUT_ON_HOLD => 'tabler--player-pause',
            self::RESUMED => 'tabler--player-play',
            self::PARENT_CHANGED => 'tabler--subtask',
        };
    }
}

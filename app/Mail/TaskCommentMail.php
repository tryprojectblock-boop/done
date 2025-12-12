<?php

namespace App\Mail;

use App\Models\User;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $taskUrl;

    public function __construct(
        public Task $task,
        public TaskComment $comment,
        public User $recipient,
        public User $commenter
    ) {
        $this->taskUrl = route('tasks.show', $task->uuid);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Comment on Task: {$this->task->title} ({$this->task->task_number})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-comment',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

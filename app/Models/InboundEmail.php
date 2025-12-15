<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Workspace\Models\Workspace;
use App\Modules\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundEmail extends Model
{
    protected $fillable = [
        'workspace_id',
        'message_id',
        'in_reply_to',
        'references',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body_plain',
        'body_html',
        'stripped_text',
        'stripped_html',
        'attachment_count',
        'attachments',
        'raw_payload',
        'status',
        'processed_at',
        'ticket_id',
        'is_reply',
        'error_message',
    ];

    protected $casts = [
        'attachment_count' => 'integer',
        'attachments' => 'array',
        'processed_at' => 'datetime',
        'is_reply' => 'boolean',
    ];

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the workspace this email belongs to.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the ticket/task created from this email.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'ticket_id');
    }

    /**
     * Scope for pending emails.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processed emails.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope for failed emails.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Mark email as processed.
     */
    public function markAsProcessed(int $ticketId, bool $isReply = false): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
            'ticket_id' => $ticketId,
            'is_reply' => $isReply,
        ]);
    }

    /**
     * Mark email as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}

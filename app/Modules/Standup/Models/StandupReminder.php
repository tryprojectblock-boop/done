<?php

declare(strict_types=1);

namespace App\Modules\Standup\Models;

use App\Models\User;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandupReminder extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'reminder_date',
        'sent_at',
        'standup_submitted',
    ];

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
            'sent_at' => 'datetime',
            'standup_submitted' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reminder_date', $date);
    }

    public function scopePending($query)
    {
        return $query->whereNull('sent_at');
    }

    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    public function scopeNotSubmitted($query)
    {
        return $query->where('standup_submitted', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Mark reminder as sent.
     */
    public function markAsSent(): void
    {
        $this->update(['sent_at' => now()]);
    }

    /**
     * Mark standup as submitted.
     */
    public function markAsSubmitted(): void
    {
        $this->update(['standup_submitted' => true]);
    }

    /**
     * Check if reminder has been sent.
     */
    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    /**
     * Create or get reminder for today.
     */
    public static function getOrCreateForToday(int $workspaceId, int $userId): self
    {
        return self::firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
                'reminder_date' => today(),
            ],
            [
                'standup_submitted' => false,
            ]
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilestoneActivity extends Model
{
    protected $fillable = [
        'milestone_id',
        'user_id',
        'action',
        'description',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'tabler--plus',
            'updated' => 'tabler--edit',
            'status_changed' => 'tabler--refresh',
            'task_added' => 'tabler--checkbox',
            'task_removed' => 'tabler--checkbox-x',
            'task_completed' => 'tabler--check',
            'comment_added' => 'tabler--message',
            'attachment_added' => 'tabler--paperclip',
            'owner_changed' => 'tabler--user',
            default => 'tabler--activity',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'text-success',
            'updated' => 'text-info',
            'status_changed' => 'text-warning',
            'task_completed' => 'text-success',
            'comment_added' => 'text-primary',
            default => 'text-base-content/60',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSlaSetting extends Model
{
    protected $fillable = [
        'workspace_id',
        'priority_id',
        'first_reply_days',
        'first_reply_hours',
        'first_reply_minutes',
        'next_reply_days',
        'next_reply_hours',
        'next_reply_minutes',
        'resolution_days',
        'resolution_hours',
        'resolution_minutes',
    ];

    protected $casts = [
        'first_reply_days' => 'integer',
        'first_reply_hours' => 'integer',
        'first_reply_minutes' => 'integer',
        'next_reply_days' => 'integer',
        'next_reply_hours' => 'integer',
        'next_reply_minutes' => 'integer',
        'resolution_days' => 'integer',
        'resolution_hours' => 'integer',
        'resolution_minutes' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(WorkspacePriority::class, 'priority_id');
    }

    /**
     * Get first reply time in total minutes.
     */
    public function getFirstReplyMinutesTotal(): int
    {
        return ($this->first_reply_days * 24 * 60) + ($this->first_reply_hours * 60) + $this->first_reply_minutes;
    }

    /**
     * Get next reply time in total minutes.
     */
    public function getNextReplyMinutesTotal(): int
    {
        return ($this->next_reply_days * 24 * 60) + ($this->next_reply_hours * 60) + $this->next_reply_minutes;
    }

    /**
     * Get resolution time in total minutes.
     */
    public function getResolutionMinutesTotal(): int
    {
        return ($this->resolution_days * 24 * 60) + ($this->resolution_hours * 60) + $this->resolution_minutes;
    }

    /**
     * Format time as readable string.
     */
    public static function formatTime(int $days, int $hours, int $minutes): string
    {
        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        return implode(' ', $parts) ?: '0m';
    }

    /**
     * Create default SLA settings for all priorities in a workspace.
     */
    public static function createDefaults(Workspace $workspace): void
    {
        $defaults = [
            'Low' => ['first' => [0, 4, 0], 'next' => [0, 8, 0], 'resolution' => [3, 0, 0]],
            'Medium' => ['first' => [0, 2, 0], 'next' => [0, 4, 0], 'resolution' => [2, 0, 0]],
            'High' => ['first' => [0, 1, 0], 'next' => [0, 2, 0], 'resolution' => [1, 0, 0]],
            'Critical' => ['first' => [0, 0, 30], 'next' => [0, 1, 0], 'resolution' => [0, 8, 0]],
        ];

        foreach ($workspace->priorities as $priority) {
            $default = $defaults[$priority->name] ?? $defaults['Medium'];

            $workspace->slaSettings()->create([
                'priority_id' => $priority->id,
                'first_reply_days' => $default['first'][0],
                'first_reply_hours' => $default['first'][1],
                'first_reply_minutes' => $default['first'][2],
                'next_reply_days' => $default['next'][0],
                'next_reply_hours' => $default['next'][1],
                'next_reply_minutes' => $default['next'][2],
                'resolution_days' => $default['resolution'][0],
                'resolution_hours' => $default['resolution'][1],
                'resolution_minutes' => $default['resolution'][2],
            ]);
        }
    }
}

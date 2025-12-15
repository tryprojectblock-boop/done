<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceWorkingHour extends Model
{
    protected $fillable = [
        'workspace_id',
        'day',
        'is_enabled',
        'start_time',
        'end_time',
        'total_hours',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'total_hours' => 'decimal:1',
    ];

    public const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Create default working hours for a workspace (Mon-Fri 9-5).
     */
    public static function createDefaults(Workspace $workspace): void
    {
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach (self::DAYS as $day) {
            $workspace->workingHours()->create([
                'day' => $day,
                'is_enabled' => in_array($day, $weekdays),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'total_hours' => 8.0,
            ]);
        }
    }

    /**
     * Calculate total hours from start and end time.
     */
    public function calculateTotalHours(): float
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        if ($end < $start) {
            $end += 24 * 60 * 60; // Add 24 hours for overnight shifts
        }

        $diffMinutes = ($end - $start) / 60;
        return round($diffMinutes / 60, 1);
    }
}

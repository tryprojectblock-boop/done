<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceHoliday extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'date',
        'working_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'working_hours' => 'decimal:1',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('date');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->startOfDay());
    }

    /**
     * Check if this is a full holiday (no work).
     */
    public function isFullHoliday(): bool
    {
        return $this->working_hours == 0;
    }

    /**
     * Check if this is a reduced hours day.
     */
    public function isReducedHours(): bool
    {
        return $this->working_hours > 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSlaRule extends Model
{
    protected $fillable = [
        'workspace_id',
        'department_id',
        'priority_id',
        'assigned_user_id',
        'status',
        'resolution_hours',
        'escalation_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'resolution_hours' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(WorkspaceDepartment::class, 'department_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(WorkspacePriority::class, 'priority_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get formatted resolution time.
     */
    public function getFormattedResolutionTime(): string
    {
        $hours = $this->resolution_hours;
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return $days . 'd ' . ($remainingHours > 0 ? $remainingHours . 'h' : '');
        }
        return $hours . 'h';
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspacePriority extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Create default priorities for a workspace.
     */
    public static function createDefaults(Workspace $workspace): void
    {
        $defaults = [
            ['name' => 'Low', 'color' => '#22c55e', 'sort_order' => 1],
            ['name' => 'Medium', 'color' => '#eab308', 'sort_order' => 2],
            ['name' => 'High', 'color' => '#f97316', 'sort_order' => 3],
            ['name' => 'Critical', 'color' => '#ef4444', 'sort_order' => 4],
        ];

        foreach ($defaults as $priority) {
            $workspace->priorities()->create($priority);
        }
    }
}

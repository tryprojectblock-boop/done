<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTicketRule extends Model
{
    protected $fillable = [
        'workspace_id',
        'department_id',
        'assigned_user_id',
        'backup_user_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
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

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function backupUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backup_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

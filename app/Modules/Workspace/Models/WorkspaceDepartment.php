<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceDepartment extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'is_public',
        'incharge_id',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function incharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incharge_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Task\Models;

use App\Modules\Auth\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'color',
    ];

    // ==================== RELATIONSHIPS ====================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag')
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // ==================== HELPER METHODS ====================

    public function getColorClass(): string
    {
        return match ($this->color) {
            'red' => 'badge-error',
            'orange' => 'badge-warning',
            'yellow' => 'badge-warning',
            'green' => 'badge-success',
            'blue' => 'badge-info',
            'purple' => 'badge-secondary',
            'pink' => 'badge-accent',
            'gray' => 'badge-neutral',
            default => 'badge-primary',
        };
    }
}

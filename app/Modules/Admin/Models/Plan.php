<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Enums\PlanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'workspace_limit',
        'team_member_limit',
        'storage_limit_gb',
        'price_1_month',
        'price_3_month',
        'price_6_month',
        'price_12_month',
        'price_3_year',
        'price_5_year',
        'is_active',
        'is_popular',
        'features',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PlanType::class,
            'workspace_limit' => 'integer',
            'team_member_limit' => 'integer',
            'storage_limit_gb' => 'integer',
            'price_1_month' => 'decimal:2',
            'price_3_month' => 'decimal:2',
            'price_6_month' => 'decimal:2',
            'price_12_month' => 'decimal:2',
            'price_3_year' => 'decimal:2',
            'price_5_year' => 'decimal:2',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'features' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function isFree(): bool
    {
        return $this->type === PlanType::FREE;
    }

    public function isPaid(): bool
    {
        return $this->type === PlanType::PAID;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

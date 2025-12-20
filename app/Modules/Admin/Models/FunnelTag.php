<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelTag extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_funnel_tags')
            ->withPivot('tagged_at');
    }

    public function funnels(): HasMany
    {
        return $this->hasMany(Funnel::class, 'trigger_tag_id');
    }

    public function conditionSteps(): HasMany
    {
        return $this->hasMany(FunnelStep::class, 'condition_tag_id');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}

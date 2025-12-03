<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (! $model->getAttribute($model->getTenantIdColumn()) && $tenantId = static::getCurrentTenantId()) {
                $model->setAttribute($model->getTenantIdColumn(), $tenantId);
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = static::getCurrentTenantId()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.' . $builder->getModel()->getTenantIdColumn(),
                    $tenantId
                );
            }
        });
    }

    public function getTenantIdColumn(): string
    {
        return property_exists($this, 'tenantIdColumn') ? $this->tenantIdColumn : 'tenant_id';
    }

    protected static function getCurrentTenantId(): ?int
    {
        // Will be replaced with actual tenant resolution when tenancy package is installed
        // For now, return null or use a session/context value
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        return session('current_tenant_id');
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}

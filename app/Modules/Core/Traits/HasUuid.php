<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = (string) Str::uuid();
            }
        });
    }

    public function getUuidColumn(): string
    {
        return property_exists($this, 'uuidColumn') ? $this->uuidColumn : 'uuid';
    }

    public static function findByUuid(string $uuid): ?static
    {
        return static::where((new static)->getUuidColumn(), $uuid)->first();
    }

    public static function findByUuidOrFail(string $uuid): static
    {
        return static::where((new static)->getUuidColumn(), $uuid)->firstOrFail();
    }

    public function getRouteKeyName(): string
    {
        return $this->getUuidColumn();
    }
}

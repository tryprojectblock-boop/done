<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum CompanySize: string
{
    case SOLO = 'solo';
    case SMALL = '2-10';
    case MEDIUM = '11-50';
    case LARGE = '51-200';
    case ENTERPRISE = '201-500';
    case CORPORATION = '500+';

    public function label(): string
    {
        return match ($this) {
            self::SOLO => 'Just me',
            self::SMALL => '2-10 employees',
            self::MEDIUM => '11-50 employees',
            self::LARGE => '51-200 employees',
            self::ENTERPRISE => '201-500 employees',
            self::CORPORATION => '500+ employees',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $size) => [
            'value' => $size->value,
            'label' => $size->label(),
        ], self::cases());
    }
}

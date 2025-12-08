<?php

declare(strict_types=1);

namespace App\Modules\Admin\Enums;

enum PlanType: string
{
    case FREE = 'free';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::PAID => 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FREE => 'ghost',
            self::PAID => 'primary',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Modules\Core\Support\DataTransferObject;

final class CompleteProfileDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $password,
    ) {}
}

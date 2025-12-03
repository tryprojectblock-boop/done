<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Modules\Core\Support\DataTransferObject;

final class RegisterEmailDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}

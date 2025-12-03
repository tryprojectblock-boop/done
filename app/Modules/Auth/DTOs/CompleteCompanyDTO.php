<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use App\Modules\Core\Support\DataTransferObject;

final class CompleteCompanyDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $companyName,
        public readonly CompanySize $companySize,
        public readonly IndustryType $industryType,
        public readonly ?string $websiteProtocol = 'https',
        public readonly ?string $websiteUrl = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            companyName: $data['company_name'],
            companySize: $data['company_size'] instanceof CompanySize
                ? $data['company_size']
                : CompanySize::from($data['company_size']),
            industryType: $data['industry_type'] instanceof IndustryType
                ? $data['industry_type']
                : IndustryType::from($data['industry_type']),
            websiteProtocol: $data['website_protocol'] ?? 'https',
            websiteUrl: $data['website_url'] ?? null,
        );
    }
}

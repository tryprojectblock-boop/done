<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum IndustryType: string
{
    case TECHNOLOGY = 'technology';
    case SOFTWARE = 'software';
    case SAAS = 'saas';
    case ECOMMERCE = 'ecommerce';
    case FINANCE = 'finance';
    case HEALTHCARE = 'healthcare';
    case EDUCATION = 'education';
    case MARKETING = 'marketing';
    case AGENCY = 'agency';
    case CONSULTING = 'consulting';
    case MANUFACTURING = 'manufacturing';
    case RETAIL = 'retail';
    case REAL_ESTATE = 'real_estate';
    case MEDIA = 'media';
    case ENTERTAINMENT = 'entertainment';
    case NON_PROFIT = 'non_profit';
    case GOVERNMENT = 'government';
    case LEGAL = 'legal';
    case CONSTRUCTION = 'construction';
    case HOSPITALITY = 'hospitality';
    case TRANSPORTATION = 'transportation';
    case ENERGY = 'energy';
    case AGRICULTURE = 'agriculture';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TECHNOLOGY => 'Technology',
            self::SOFTWARE => 'Software Development',
            self::SAAS => 'SaaS',
            self::ECOMMERCE => 'E-commerce',
            self::FINANCE => 'Finance & Banking',
            self::HEALTHCARE => 'Healthcare',
            self::EDUCATION => 'Education',
            self::MARKETING => 'Marketing & Advertising',
            self::AGENCY => 'Creative Agency',
            self::CONSULTING => 'Consulting',
            self::MANUFACTURING => 'Manufacturing',
            self::RETAIL => 'Retail',
            self::REAL_ESTATE => 'Real Estate',
            self::MEDIA => 'Media & Publishing',
            self::ENTERTAINMENT => 'Entertainment',
            self::NON_PROFIT => 'Non-Profit',
            self::GOVERNMENT => 'Government',
            self::LEGAL => 'Legal Services',
            self::CONSTRUCTION => 'Construction',
            self::HOSPITALITY => 'Hospitality & Tourism',
            self::TRANSPORTATION => 'Transportation & Logistics',
            self::ENERGY => 'Energy & Utilities',
            self::AGRICULTURE => 'Agriculture',
            self::OTHER => 'Other',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }

    public static function searchableOptions(): array
    {
        return collect(self::cases())
            ->map(fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'searchTerms' => strtolower($type->label() . ' ' . $type->value),
            ])
            ->toArray();
    }
}

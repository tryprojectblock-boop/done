<?php

declare(strict_types=1);

namespace App\Modules\Document\Enums;

enum CollaboratorRole: string
{
    case EDITOR = 'editor';
    case READER = 'reader';

    public function label(): string
    {
        return match ($this) {
            self::EDITOR => 'Editor',
            self::READER => 'Reader',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EDITOR => 'Can edit the document and add comments',
            self::READER => 'Can only view and add comments',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EDITOR => 'tabler--edit',
            self::READER => 'tabler--eye',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EDITOR => 'primary',
            self::READER => 'secondary',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }
        return $options;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Standup\Enums;

enum MoodType: string
{
    case GREAT = 'great';
    case GOOD = 'good';
    case OKAY = 'okay';
    case CONCERNED = 'concerned';
    case STRUGGLING = 'struggling';

    public function emoji(): string
    {
        return match ($this) {
            self::GREAT => '😊',
            self::GOOD => '🙂',
            self::OKAY => '😐',
            self::CONCERNED => '😕',
            self::STRUGGLING => '😢',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::GREAT => 'Great',
            self::GOOD => 'Good',
            self::OKAY => 'Okay',
            self::CONCERNED => 'Concerned',
            self::STRUGGLING => 'Struggling',
        };
    }

    public function score(): int
    {
        return match ($this) {
            self::GREAT => 5,
            self::GOOD => 4,
            self::OKAY => 3,
            self::CONCERNED => 2,
            self::STRUGGLING => 1,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GREAT => 'success',
            self::GOOD => 'success',
            self::OKAY => 'warning',
            self::CONCERNED => 'warning',
            self::STRUGGLING => 'error',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'emoji' => $case->emoji(),
            'label' => $case->label(),
        ], self::cases());
    }

    /**
     * Calculate average mood score from entries.
     */
    public static function averageScore(array $moods): float
    {
        if (empty($moods)) {
            return 3.0; // Default to "Okay"
        }

        $total = 0;
        $count = 0;

        foreach ($moods as $mood) {
            if ($mood instanceof self) {
                $total += $mood->score();
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 1) : 3.0;
    }
}

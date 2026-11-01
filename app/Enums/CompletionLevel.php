<?php

namespace App\Enums;

enum CompletionLevel: string
{
    case Story = 'story';
    case Full = 'full';
    case Platinum = 'platinum';

    /**
     * Points earned by a playthrough completed at this level.
     */
    public function points(): int
    {
        return match ($this) {
            self::Story => 50,
            self::Full => 100,
            self::Platinum => 200,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Story => 'História',
            self::Full => '100%',
            self::Platinum => 'Platina',
        };
    }
}

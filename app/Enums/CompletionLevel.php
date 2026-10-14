<?php

namespace App\Enums;

enum CompletionLevel: string
{
    case Story = 'story';
    case Full = 'full';
    case Platinum = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::Story => 'História',
            self::Full => '100%',
            self::Platinum => 'Platina',
        };
    }
}

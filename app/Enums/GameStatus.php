<?php

namespace App\Enums;

enum GameStatus: string
{
    case Backlog = 'backlog';
    case Playing = 'playing';
    case Completed = 'completed';
    case Dropped = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Quero zerar',
            self::Playing => 'Jogando',
            self::Completed => 'Zerado',
            self::Dropped => 'Abandonado',
        };
    }
}

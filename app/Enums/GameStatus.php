<?php

namespace App\Enums;

enum GameStatus: string
{
    case Backlog = 'backlog';
    case Playing = 'playing';
    case Paused = 'paused';
    case Completed = 'completed';
    case Dropped = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Quero zerar',
            self::Playing => 'Jogando',
            self::Paused => 'Pausado',
            self::Completed => 'Zerado',
            self::Dropped => 'Abandonado',
        };
    }
}

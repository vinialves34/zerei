<?php

namespace App\Enums;

enum LevelTitle: string
{
    case Rookie = 'rookie';
    case Player = 'player';
    case Veteran = 'veteran';
    case Legend = 'legend';

    public static function forLevel(int $level): self
    {
        return match (true) {
            $level >= 20 => self::Legend,
            $level >= 10 => self::Veteran,
            $level >= 5 => self::Player,
            default => self::Rookie,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Rookie => 'Novato',
            self::Player => 'Jogador',
            self::Veteran => 'Veterano',
            self::Legend => 'Lenda',
        };
    }
}

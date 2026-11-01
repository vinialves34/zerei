<?php

namespace App\Enums;

enum Achievement: string
{
    case FirstPlatinum = 'first-platinum';
    case TenInAYear = 'ten-in-a-year';
    case ThreeInAWeek = 'three-in-a-week';
    case FivePlatforms = 'five-platforms';

    public function label(): string
    {
        return match ($this) {
            self::FirstPlatinum => 'Primeira platina',
            self::TenInAYear => '10 zeradas no ano',
            self::ThreeInAWeek => '3 zeradas em uma semana',
            self::FivePlatforms => '5 plataformas diferentes',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FirstPlatinum => 'Zere um jogo com a platina.',
            self::TenInAYear => 'Zere 10 jogos dentro do mesmo ano.',
            self::ThreeInAWeek => 'Zere 3 jogos em até 7 dias seguidos.',
            self::FivePlatforms => 'Zere jogos em 5 plataformas diferentes.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FirstPlatinum => 'trophy',
            self::TenInAYear => 'calendar',
            self::ThreeInAWeek => 'zap',
            self::FivePlatforms => 'gamepad',
        };
    }
}

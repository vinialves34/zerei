<?php

namespace App\Enums;

enum GameSort: string
{
    case TitleAsc = 'title-asc';
    case TitleDesc = 'title-desc';

    public function label(): string
    {
        return match ($this) {
            self::TitleAsc => 'Título (A–Z)',
            self::TitleDesc => 'Título (Z–A)',
        };
    }
}

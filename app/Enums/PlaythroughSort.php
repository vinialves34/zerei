<?php

namespace App\Enums;

enum PlaythroughSort: string
{
    case DateDesc = 'date-desc';
    case DateAsc = 'date-asc';
    case RatingDesc = 'rating-desc';
    case RatingAsc = 'rating-asc';

    public function label(): string
    {
        return match ($this) {
            self::DateDesc => 'Mais recentes',
            self::DateAsc => 'Mais antigas',
            self::RatingDesc => 'Maior nota',
            self::RatingAsc => 'Menor nota',
        };
    }
}

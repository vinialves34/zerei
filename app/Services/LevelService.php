<?php

namespace App\Services;

use App\Enums\CompletionLevel;
use App\Enums\LevelTitle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LevelService
{
    /**
     * Scale of the level curve: level N starts at POINTS_STEP × N × (N − 1) total points.
     */
    private const int POINTS_STEP = 50;

    /**
     * Total points required to reach the given level.
     */
    public function pointsForLevel(int $level): int
    {
        return self::POINTS_STEP * $level * ($level - 1);
    }

    /**
     * Level reached with the given total points; everyone starts at level 1.
     */
    public function levelForPoints(int $points): int
    {
        $level = (int) floor((1 + sqrt(1 + 4 * $points / self::POINTS_STEP)) / 2);

        while ($this->pointsForLevel($level + 1) <= $points) {
            $level++;
        }

        while ($level > 1 && $this->pointsForLevel($level) > $points) {
            $level--;
        }

        return max(1, $level);
    }

    /**
     * Points of the user, summed from the playthroughs, so editing or removing one adjusts
     * everything. Restricted to a calendar year when given.
     */
    public function points(User $user, ?int $year = null): int
    {
        return $user->playthroughs()
            ->when($year !== null, fn (Builder $query) => $query->completedInYear($year))
            ->toBase()
            ->selectRaw('playthroughs.completion_level, count(*) as total')
            ->groupBy('playthroughs.completion_level')
            ->pluck('total', 'completion_level')
            ->reduce(
                fn (int $sum, int $total, string $level) => $sum + CompletionLevel::from($level)->points() * $total,
                0,
            );
    }

    /**
     * Level, title and progress towards the next level.
     *
     * @return array{
     *     points: int,
     *     level: int,
     *     title: LevelTitle,
     *     pointsIntoLevel: int,
     *     pointsForNextLevel: int,
     *     remaining: int,
     *     percent: int,
     * }
     */
    public function progress(User $user): array
    {
        $points = $this->points($user);
        $level = $this->levelForPoints($points);

        $pointsIntoLevel = $points - $this->pointsForLevel($level);
        $pointsForNextLevel = $this->pointsForLevel($level + 1) - $this->pointsForLevel($level);

        return [
            'points' => $points,
            'level' => $level,
            'title' => LevelTitle::forLevel($level),
            'pointsIntoLevel' => $pointsIntoLevel,
            'pointsForNextLevel' => $pointsForNextLevel,
            'remaining' => $pointsForNextLevel - $pointsIntoLevel,
            'percent' => intdiv($pointsIntoLevel * 100, $pointsForNextLevel),
        ];
    }
}

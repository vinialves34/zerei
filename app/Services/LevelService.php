<?php

namespace App\Services;

use App\Enums\LevelTitle;
use App\Models\Playthrough;
use App\Models\User;
use App\Models\YearlyGoal;
use Illuminate\Support\Collection;

class LevelService
{
    /**
     * Scale of the level curve: level N starts at POINTS_STEP × N × (N − 1) total points.
     */
    private const int POINTS_STEP = 50;

    /**
     * Extra points for each year in which the yearly goal was reached.
     */
    public const int GOAL_BONUS = 300;

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
     * Points of the user, summed from the playthroughs and the yearly goals reached, so
     * editing or removing anything adjusts everything. Restricted to a calendar year when given.
     *
     * Only the first playthrough of each game earns full points; the following ones earn half.
     */
    public function points(User $user, ?int $year = null): int
    {
        $earned = $this->playthroughPoints($user);

        $bonusYears = $user->yearlyGoals()->get()
            ->filter(fn (YearlyGoal $goal) => $earned->where('year', $goal->year)->count() >= $goal->target)
            ->pluck('year');

        if ($year !== null) {
            $earned = $earned->where('year', $year);
            $bonusYears = $bonusYears->filter(fn (int $goalYear) => $goalYear === $year);
        }

        return $earned->sum('points') + self::GOAL_BONUS * $bonusYears->count();
    }

    /**
     * Returns the new level when the user went up since the last one they were told about, and
     * remembers the current level. The first call only records it, so nobody is greeted for
     * progress made before levels were announced.
     */
    public function claimLevelUp(User $user, int $currentLevel): ?int
    {
        $lastSeen = $user->last_seen_level;

        if ($lastSeen !== $currentLevel) {
            $user->forceFill(['last_seen_level' => $currentLevel])->saveQuietly();
        }

        return $lastSeen !== null && $currentLevel > $lastSeen ? $currentLevel : null;
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

    /**
     * Points of each playthrough, with the year it was completed in.
     *
     * @return Collection<int, array{year: int, points: int}>
     */
    private function playthroughPoints(User $user): Collection
    {
        return $user->playthroughs()
            ->orderBy('completed_at')
            ->orderBy('playthroughs.id')
            ->get()
            ->groupBy('game_id')
            ->flatMap(fn (Collection $replays) => $replays->values()->map(fn (Playthrough $playthrough, int $position) => [
                'year' => $playthrough->completed_at->year,
                'points' => $position === 0
                    ? $playthrough->completion_level->points()
                    : intdiv($playthrough->completion_level->points(), 2),
            ]))
            ->values();
    }
}

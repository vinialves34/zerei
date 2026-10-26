<?php

namespace App\Services;

use App\Models\User;
use App\Models\YearlyGoal;

class GoalService
{
    public function forYear(User $user, int $year): ?YearlyGoal
    {
        return $user->yearlyGoals()->where('year', $year)->first();
    }

    /**
     * Create the goal of the year, or change its target when one already exists.
     */
    public function set(User $user, int $year, int $target): YearlyGoal
    {
        return $user->yearlyGoals()->updateOrCreate(['year' => $year], ['target' => $target]);
    }

    public function remove(User $user, int $year): void
    {
        $user->yearlyGoals()->where('year', $year)->delete();
    }

    /**
     * Progress towards the goal, or null when the year has no goal.
     *
     * @return array{target: int, completed: int, remaining: int, percent: int, reached: bool}|null
     */
    public function progress(User $user, int $year, int $completed): ?array
    {
        $goal = $this->forYear($user, $year);

        if ($goal === null) {
            return null;
        }

        return [
            'target' => $goal->target,
            'completed' => $completed,
            'remaining' => max(0, $goal->target - $completed),
            'percent' => min(100, intdiv($completed * 100, $goal->target)),
            'reached' => $completed >= $goal->target,
        ];
    }
}

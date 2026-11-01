<?php

namespace App\Services;

use App\Enums\Achievement;
use App\Enums\CompletionLevel;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AchievementService
{
    /**
     * Every achievement with whether the user has unlocked it. They are calculated from the
     * playthroughs, so editing or removing one can lock an achievement again.
     *
     * @return Collection<int, array{achievement: Achievement, unlocked: bool}>
     */
    public function forUser(User $user): Collection
    {
        $playthroughs = $user->playthroughs()->get();

        return collect(Achievement::cases())->map(fn (Achievement $achievement) => [
            'achievement' => $achievement,
            'unlocked' => match ($achievement) {
                Achievement::FirstPlatinum => $playthroughs->contains('completion_level', CompletionLevel::Platinum),
                Achievement::TenInAYear => $this->hasCompletionsInAYear($playthroughs, 10),
                Achievement::ThreeInAWeek => $this->hasCompletionsInAWeek($playthroughs, 3),
                Achievement::FivePlatforms => $playthroughs->pluck('platform')->unique()->count() >= 5,
            },
        ]);
    }

    /**
     * @param  Collection<int, Playthrough>  $playthroughs
     */
    private function hasCompletionsInAYear(Collection $playthroughs, int $count): bool
    {
        return $playthroughs->countBy(fn (Playthrough $playthrough) => $playthrough->completed_at->year)
            ->contains(fn (int $total) => $total >= $count);
    }

    /**
     * Whether some window of seven consecutive days holds the given number of completions.
     *
     * @param  Collection<int, Playthrough>  $playthroughs
     */
    private function hasCompletionsInAWeek(Collection $playthroughs, int $count): bool
    {
        $days = $playthroughs
            ->map(fn (Playthrough $playthrough) => intdiv(Carbon::parse($playthrough->completed_at->toDateString(), 'UTC')->getTimestamp(), 86400))
            ->sort()
            ->values();

        return $days->contains(fn (int $day, int $index) => $days->has($index + $count - 1)
            && $days[$index + $count - 1] - $day <= 6);
    }
}

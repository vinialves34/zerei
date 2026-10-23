<?php

namespace App\Services;

use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Number of playthroughs per year, newest year first.
     *
     * @return Collection<int, int>
     */
    public function completionsByYear(User $user): Collection
    {
        return $user->playthroughs()
            ->pluck('completed_at')
            ->countBy(fn ($date) => $date->year)
            ->sortKeysDesc();
    }

    /**
     * Number of playthroughs per month of the year, with all twelve months present.
     *
     * @return Collection<int, int>
     */
    public function monthlyCounts(User $user, int $year): Collection
    {
        $countsByMonth = $user->playthroughs()
            ->completedInYear($year)
            ->pluck('completed_at')
            ->countBy(fn ($date) => $date->month);

        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => $countsByMonth->get($month, 0)]);
    }

    /**
     * Playthroughs of the year grouped by month (oldest first), with all twelve months present.
     *
     * @return Collection<int, Collection<int, Playthrough>>
     */
    public function timeline(User $user, int $year): Collection
    {
        $byMonth = $user->playthroughs()
            ->with('game')
            ->completedInYear($year)
            ->orderBy('completed_at')
            ->orderBy('playthroughs.id')
            ->get()
            ->groupBy(fn (Playthrough $playthrough) => $playthrough->completed_at->month);

        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => $byMonth->get($month, collect())]);
    }
}

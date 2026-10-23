<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private ReportService $reports) {}

    /**
     * @return array{
     *     year: int,
     *     monthlyCounts: Collection<int, int>,
     *     completedThisYear: int,
     *     backlogCount: int,
     *     playingGames: Collection<int, Game>,
     *     recentPlaythroughs: Collection<int, Playthrough>,
     * }
     */
    public function summary(User $user, int $year): array
    {
        $monthlyCounts = $this->reports->monthlyCounts($user, $year);

        return [
            'year' => $year,
            'monthlyCounts' => $monthlyCounts,
            'completedThisYear' => $monthlyCounts->sum(),
            'backlogCount' => $user->games()->where('status', GameStatus::Backlog)->count(),
            'playingGames' => $user->games()->where('status', GameStatus::Playing)->orderBy('title')->get(),
            'recentPlaythroughs' => $user->playthroughs()
                ->with('game')
                ->orderByDesc('completed_at')
                ->orderByDesc('playthroughs.id')
                ->limit(4)
                ->get(),
        ];
    }
}

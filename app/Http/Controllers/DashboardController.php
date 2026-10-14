<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $year = now()->year;

        $completedByMonth = $user->playthroughs()
            ->completedInYear($year)
            ->pluck('completed_at')
            ->countBy(fn ($date) => $date->month);

        $monthlyCounts = collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => $completedByMonth->get($month, 0)]);

        return view('dashboard', [
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
        ]);
    }
}

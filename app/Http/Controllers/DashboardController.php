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

        return view('dashboard', [
            'year' => $year,
            'completedThisYear' => $user->playthroughs()->completedInYear($year)->count(),
            'backlogCount' => $user->games()->where('status', GameStatus::Backlog)->count(),
            'playingGames' => $user->games()->where('status', GameStatus::Playing)->orderBy('title')->get(),
            'recentPlaythroughs' => $user->playthroughs()
                ->with('game')
                ->orderByDesc('completed_at')
                ->orderByDesc('playthroughs.id')
                ->limit(5)
                ->get(),
        ]);
    }
}

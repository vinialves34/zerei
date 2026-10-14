<?php

namespace App\Http\Controllers;

use App\Models\Playthrough;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class YearController extends Controller
{
    public function index(Request $request): View
    {
        $countsByYear = $request->user()
            ->playthroughs()
            ->pluck('completed_at')
            ->countBy(fn ($date) => $date->year)
            ->sortKeysDesc();

        return view('years.index', ['countsByYear' => $countsByYear]);
    }

    public function show(Request $request, int $year): View
    {
        abort_unless($year >= 1970 && $year <= 2100, 404);

        $playthroughs = $request->user()
            ->playthroughs()
            ->with('game')
            ->completedInYear($year)
            ->orderBy('completed_at')
            ->orderBy('playthroughs.id')
            ->get();

        $byMonth = $playthroughs->groupBy(fn ($playthrough) => $playthrough->completed_at->month);

        /** @var Collection<int, Collection<int, Playthrough>> $timeline */
        $timeline = collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => $byMonth->get($month, collect())]);

        return view('years.show', [
            'year' => $year,
            'timeline' => $timeline,
            'total' => $playthroughs->count(),
        ]);
    }
}

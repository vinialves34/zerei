<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YearController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request): View
    {
        return view('years.index', [
            'countsByYear' => $this->reports->completionsByYear($request->user()),
        ]);
    }

    public function show(Request $request, int $year): View
    {
        abort_unless($year >= 1970 && $year <= 2100, 404);

        $timeline = $this->reports->timeline($request->user(), $year);

        return view('years.show', [
            'year' => $year,
            'timeline' => $timeline,
            'total' => $timeline->sum(fn ($playthroughs) => $playthroughs->count()),
        ]);
    }
}

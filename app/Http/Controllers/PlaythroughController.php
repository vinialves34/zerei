<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaythroughRequest;
use App\Http\Requests\UpdatePlaythroughRequest;
use App\Models\Playthrough;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlaythroughController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $year = $request->integer('year') ?: null;

        $playthroughs = $user->playthroughs()
            ->with('game')
            ->when($year, fn ($query) => $query->completedInYear($year))
            ->orderByDesc('completed_at')
            ->orderByDesc('playthroughs.id')
            ->paginate(20)
            ->withQueryString();

        $years = $user->playthroughs()
            ->pluck('completed_at')
            ->map(fn ($date) => $date->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('playthroughs.index', [
            'playthroughs' => $playthroughs,
            'years' => $years,
            'year' => $year,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('playthroughs.create', [
            'titles' => $user->games()->orderBy('title')->pluck('title'),
            'title' => $user->games()->find($request->integer('game'))?->title,
        ]);
    }

    public function store(StorePlaythroughRequest $request): RedirectResponse
    {
        $game = $request->user()->games()->firstOrCreate(['title' => $request->validated('title')]);

        $game->registerPlaythrough($request->safe()->except('title'));

        return redirect()
            ->route('playthroughs.index')
            ->with('success', 'Zerada registrada.');
    }

    public function edit(Playthrough $playthrough): View
    {
        Gate::authorize('update', $playthrough);

        return view('playthroughs.edit', ['playthrough' => $playthrough->load('game')]);
    }

    public function update(UpdatePlaythroughRequest $request, Playthrough $playthrough): RedirectResponse
    {
        $playthrough->update($request->validated());

        return redirect()
            ->route('playthroughs.index')
            ->with('success', 'Zerada atualizada.');
    }

    public function destroy(Playthrough $playthrough): RedirectResponse
    {
        Gate::authorize('delete', $playthrough);

        $playthrough->game->removePlaythrough($playthrough);

        return redirect()
            ->route('playthroughs.index')
            ->with('success', 'Zerada removida.');
    }
}

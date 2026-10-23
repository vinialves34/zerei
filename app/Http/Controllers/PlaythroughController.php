<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaythroughRequest;
use App\Http\Requests\UpdatePlaythroughRequest;
use App\Models\Playthrough;
use App\Services\GameService;
use App\Services\PlaythroughService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlaythroughController extends Controller
{
    public function __construct(
        private PlaythroughService $playthroughs,
        private GameService $games,
        private ReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $year = $request->integer('year') ?: null;

        return view('playthroughs.index', [
            'playthroughs' => $this->playthroughs->paginate($user, $year)->withQueryString(),
            'years' => $this->reports->completionsByYear($user)->keys(),
            'year' => $year,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('playthroughs.create', [
            'titles' => $this->games->titles($user),
            'title' => $this->games->findForUser($user, $request->integer('game'))?->title,
        ]);
    }

    public function store(StorePlaythroughRequest $request): RedirectResponse
    {
        $this->playthroughs->register(
            $request->user(),
            $request->validated('title'),
            $request->safe()->except(['title', 'rawg_id', 'cover_url']),
            $request->safe()->only(['rawg_id', 'cover_url']),
        );

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
        $this->playthroughs->update($playthrough, $request->validated());

        return redirect()
            ->route('playthroughs.index')
            ->with('success', 'Zerada atualizada.');
    }

    public function destroy(Playthrough $playthrough): RedirectResponse
    {
        Gate::authorize('delete', $playthrough);

        $this->playthroughs->remove($playthrough);

        return redirect()
            ->route('playthroughs.index')
            ->with('success', 'Zerada removida.');
    }
}

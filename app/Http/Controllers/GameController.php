<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Http\Requests\RawgSearchRequest;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(private GameService $games) {}

    public function index(Request $request): View
    {
        $status = $request->enum('status', GameStatus::class);

        return view('games.index', [
            'games' => $this->games->paginate($request->user(), $status)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function rawgSearch(RawgSearchRequest $request): JsonResponse
    {
        return response()->json($this->games->searchRawg($request->validated('q')));
    }

    public function create(): View
    {
        return view('games.create');
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        $game = $this->games->create($request->user(), $request->validated());

        return redirect()
            ->route('games.show', $game)
            ->with('success', 'Jogo cadastrado.');
    }

    public function show(Game $game): View
    {
        Gate::authorize('view', $game);

        return view('games.show', ['game' => $this->games->withPlaythroughs($game)]);
    }

    public function edit(Game $game): View
    {
        Gate::authorize('update', $game);

        return view('games.edit', ['game' => $game]);
    }

    public function update(UpdateGameRequest $request, Game $game): RedirectResponse
    {
        $this->games->update($game, $request->validated());

        return redirect()
            ->route('games.show', $game)
            ->with('success', 'Jogo atualizado.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        Gate::authorize('delete', $game);

        $this->games->delete($game);

        return redirect()
            ->route('games.index')
            ->with('success', 'Jogo removido.');
    }
}

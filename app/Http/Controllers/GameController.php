<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->enum('status', GameStatus::class);

        $games = $request->user()
            ->games()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount('playthroughs')
            ->orderBy('title')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('games.index', [
            'games' => $games,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('games.create');
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        $game = $request->user()->games()->create(array_filter($request->validated(), fn ($value) => $value !== null));

        return redirect()
            ->route('games.show', $game)
            ->with('success', 'Jogo cadastrado.');
    }

    public function show(Game $game): View
    {
        Gate::authorize('view', $game);

        return view('games.show', [
            'game' => $game->load(['playthroughs' => fn ($query) => $query->orderByDesc('completed_at')->orderByDesc('id')]),
        ]);
    }

    public function edit(Game $game): View
    {
        Gate::authorize('update', $game);

        return view('games.edit', ['game' => $game]);
    }

    public function update(UpdateGameRequest $request, Game $game): RedirectResponse
    {
        $game->update(array_filter($request->validated(), fn ($value) => $value !== null));

        return redirect()
            ->route('games.show', $game)
            ->with('success', 'Jogo atualizado.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        Gate::authorize('delete', $game);

        $game->delete();

        return redirect()
            ->route('games.index')
            ->with('success', 'Jogo removido.');
    }
}

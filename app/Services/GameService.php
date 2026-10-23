<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GameService
{
    /**
     * @return LengthAwarePaginator<int, Game>
     */
    public function paginate(User $user, ?GameStatus $status = null, int $perPage = 20): LengthAwarePaginator
    {
        return $user->games()
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->withCount('playthroughs')
            ->orderBy('title')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, string>
     */
    public function titles(User $user): Collection
    {
        return $user->games()->orderBy('title')->pluck('title');
    }

    public function findForUser(User $user, int $id): ?Game
    {
        return $user->games()->find($id);
    }

    /**
     * Load the game's playthroughs, newest first.
     */
    public function withPlaythroughs(Game $game): Game
    {
        return $game->load([
            'playthroughs' => fn ($query) => $query->orderByDesc('completed_at')->orderByDesc('id'),
        ]);
    }

    /**
     * A null status is ignored, so the game keeps its current one (or the default for a new game).
     *
     * @param  array{title: string, status?: string|null}  $attributes
     */
    public function create(User $user, array $attributes): Game
    {
        return $user->games()->create($this->withoutNulls($attributes));
    }

    /**
     * @param  array{title: string, status?: string|null}  $attributes
     */
    public function update(Game $game, array $attributes): Game
    {
        $game->update($this->withoutNulls($attributes));

        return $game;
    }

    public function delete(Game $game): void
    {
        $game->delete();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function withoutNulls(array $attributes): array
    {
        return array_filter($attributes, fn (mixed $value) => $value !== null);
    }
}

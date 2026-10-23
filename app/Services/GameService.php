<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class GameService
{
    public function __construct(private RawgService $rawg) {}

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
     * @param  array{title: string, status?: string|null, rawg_id?: int|null, cover_url?: string|null}  $attributes
     */
    public function create(User $user, array $attributes): Game
    {
        return $user->games()->create($this->withoutNulls($attributes));
    }

    /**
     * A null status is ignored, but a null cover_url removes the cover (and its RAWG id).
     *
     * @param  array{title: string, status?: string|null, rawg_id?: int|null, cover_url?: string|null}  $attributes
     */
    public function update(Game $game, array $attributes): Game
    {
        $cover = array_intersect_key($attributes, array_flip(['rawg_id', 'cover_url']));

        if (array_key_exists('cover_url', $cover) && $cover['cover_url'] === null) {
            $cover['rawg_id'] = null;
        }

        $game->update([...$this->withoutNulls(array_diff_key($attributes, $cover)), ...$cover]);

        return $game;
    }

    /**
     * Search RAWG so the user can pick the cover of a game.
     *
     * @return list<array{id: int, name: string, released: ?string, cover_url: ?string}>
     */
    public function searchRawg(string $query): array
    {
        return $this->rawg->search($query);
    }

    /**
     * Games of every user that still have no cover, for the backfill command.
     *
     * @return LazyCollection<int, Game>
     */
    public function withoutCover(): LazyCollection
    {
        return Game::query()->whereNull('cover_url')->lazyById();
    }

    /**
     * Link a game without a cover to RAWG when a result has exactly the same title.
     * Ambiguous matches are left alone so the user can choose the right one manually.
     */
    public function attachCoverByTitle(Game $game): bool
    {
        if ($game->cover_url !== null) {
            return false;
        }

        $title = $this->normalizeTitle($game->title);

        $match = collect($this->rawg->search($game->title, 5))
            ->first(fn (array $result) => $result['cover_url'] !== null && $this->normalizeTitle($result['name']) === $title);

        if ($match === null) {
            return false;
        }

        $game->update(['rawg_id' => $match['id'], 'cover_url' => $match['cover_url']]);

        return true;
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

    private function normalizeTitle(string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($title))));
    }
}

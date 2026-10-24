<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PlaythroughService
{
    /**
     * @return LengthAwarePaginator<int, Playthrough>
     */
    public function paginate(User $user, ?int $year = null, int $perPage = 10): LengthAwarePaginator
    {
        return $user->playthroughs()
            ->with('game')
            ->when($year, fn (Builder $query) => $query->completedInYear($year))
            ->orderByDesc('completed_at')
            ->orderByDesc('playthroughs.id')
            ->paginate($perPage);
    }

    /**
     * Register a completion for the game with the given title, creating the game when the
     * user does not have it yet, and mark the game as completed.
     *
     * A RAWG id the user already has reuses that game instead of creating a duplicate, and the
     * cover only fills a game that has none yet.
     *
     * @param  array{completed_at: string, platform: string, rating: int, completion_level: string}  $attributes
     * @param  array{rawg_id?: int|null, cover_url?: string|null}  $cover
     */
    public function register(User $user, string $title, array $attributes, array $cover = []): Playthrough
    {
        return DB::transaction(function () use ($user, $title, $attributes, $cover): Playthrough {
            $cover = filled($cover['cover_url'] ?? null) ? $cover : [];

            $game = (isset($cover['rawg_id']) ? $user->games()->where('rawg_id', $cover['rawg_id'])->first() : null)
                ?? $user->games()->firstOrCreate(['title' => $title], array_filter($cover));

            if ($cover !== [] && $game->cover_url === null) {
                $game->update(array_filter($cover));
            }

            $playthrough = $game->playthroughs()->create($attributes);

            $game->update(['status' => GameStatus::Completed]);

            return $playthrough;
        });
    }

    /**
     * @param  array{completed_at: string, platform: string, rating: int, completion_level: string}  $attributes
     */
    public function update(Playthrough $playthrough, array $attributes): Playthrough
    {
        $playthrough->update($attributes);

        return $playthrough;
    }

    /**
     * Delete a completion, sending the game back to the backlog when none remain.
     */
    public function remove(Playthrough $playthrough): void
    {
        DB::transaction(function () use ($playthrough): void {
            $game = $playthrough->game;

            $playthrough->delete();

            if ($game->status === GameStatus::Completed && ! $game->playthroughs()->exists()) {
                $game->update(['status' => GameStatus::Backlog]);
            }
        });
    }
}

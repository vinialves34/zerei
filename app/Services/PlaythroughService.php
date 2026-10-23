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
    public function paginate(User $user, ?int $year = null, int $perPage = 20): LengthAwarePaginator
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
     * @param  array{completed_at: string, platform: string, rating: int, completion_level: string}  $attributes
     */
    public function register(User $user, string $title, array $attributes): Playthrough
    {
        return DB::transaction(function () use ($user, $title, $attributes): Playthrough {
            $game = $user->games()->firstOrCreate(['title' => $title]);

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

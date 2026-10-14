<?php

namespace App\Models;

use App\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['title', 'status'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'backlog',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Playthrough, $this>
     */
    public function playthroughs(): HasMany
    {
        return $this->hasMany(Playthrough::class);
    }

    /**
     * Register a new completion of this game and mark it as completed.
     *
     * @param  array{completed_at: string, platform: string, rating: int, completion_level: string}  $attributes
     */
    public function registerPlaythrough(array $attributes): Playthrough
    {
        return DB::transaction(function () use ($attributes): Playthrough {
            $playthrough = $this->playthroughs()->create($attributes);

            $this->update(['status' => GameStatus::Completed]);

            return $playthrough;
        });
    }

    /**
     * Delete a completion, sending the game back to the backlog when none remain.
     */
    public function removePlaythrough(Playthrough $playthrough): void
    {
        DB::transaction(function () use ($playthrough): void {
            $playthrough->delete();

            if ($this->status === GameStatus::Completed && ! $this->playthroughs()->exists()) {
                $this->update(['status' => GameStatus::Backlog]);
            }
        });
    }
}

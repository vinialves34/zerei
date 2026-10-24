<?php

namespace App\Models;

use App\Enums\CompletionLevel;
use App\Enums\Platform;
use Database\Factories\PlaythroughFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['completed_at', 'platform', 'rating', 'completion_level'])]
class Playthrough extends Model
{
    /** @use HasFactory<PlaythroughFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'date',
            'platform' => Platform::class,
            'completion_level' => CompletionLevel::class,
            'rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Playthroughs completed with the platinum trophy.
     *
     * @param  Builder<Playthrough>  $query
     */
    #[Scope]
    protected function platinum(Builder $query): void
    {
        $query->where($this->qualifyColumn('completion_level'), CompletionLevel::Platinum);
    }

    /**
     * Playthroughs completed during the given calendar year.
     *
     * @param  Builder<Playthrough>  $query
     */
    #[Scope]
    protected function completedInYear(Builder $query, int $year): void
    {
        $query
            ->where($this->qualifyColumn('completed_at'), '>=', "{$year}-01-01")
            ->where($this->qualifyColumn('completed_at'), '<', ($year + 1).'-01-01');
    }
}

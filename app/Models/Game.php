<?php

namespace App\Models;

use App\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'status', 'rawg_id', 'cover_url'])]
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
     * Games whose title contains the term, ignoring case and treating % and _ literally.
     *
     * @param  Builder<Game>  $query
     */
    #[Scope]
    protected function titleContaining(Builder $query, string $term): void
    {
        $query->whereRaw("{$this->qualifyColumn('title')} LIKE ? ESCAPE ?", [static::likePattern($term), '\\']);
    }

    /**
     * LIKE pattern that matches the term anywhere, escaping the wildcard characters.
     */
    public static function likePattern(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }
}

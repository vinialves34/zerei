<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(GameService::class);
});

test('paginate returns only the games of the user ordered by title with their playthrough count', function () {
    Game::factory()->for($this->user)->create(['title' => 'Zelda']);
    Game::factory()->for($this->user)->completed()->has(Playthrough::factory()->count(2))->create(['title' => 'Celeste']);
    Game::factory()->create(['title' => 'Alheio']);

    $games = $this->service->paginate($this->user);

    expect($games->pluck('title')->all())->toBe(['Celeste', 'Zelda'])
        ->and($games->first()->playthroughs_count)->toBe(2);
});

test('paginate filters by status', function () {
    Game::factory()->for($this->user)->create(['title' => 'No backlog']);
    Game::factory()->for($this->user)->playing()->create(['title' => 'Em andamento']);

    $games = $this->service->paginate($this->user, GameStatus::Playing);

    expect($games->pluck('title')->all())->toBe(['Em andamento']);
});

test('titles lists the titles of the user alphabetically', function () {
    Game::factory()->for($this->user)->create(['title' => 'Zelda']);
    Game::factory()->for($this->user)->create(['title' => 'Celeste']);
    Game::factory()->create(['title' => 'Alheio']);

    expect($this->service->titles($this->user)->all())->toBe(['Celeste', 'Zelda']);
});

test('findForUser does not return a game of another user', function () {
    $own = Game::factory()->for($this->user)->create();
    $other = Game::factory()->create();

    expect($this->service->findForUser($this->user, $own->id)?->is($own))->toBeTrue()
        ->and($this->service->findForUser($this->user, $other->id))->toBeNull();
});

test('withPlaythroughs loads the playthroughs newest first', function () {
    $game = Game::factory()->completed()->create();
    Playthrough::factory()->for($game)->create(['completed_at' => '2024-01-01']);
    Playthrough::factory()->for($game)->create(['completed_at' => '2025-01-01']);

    $loaded = $this->service->withPlaythroughs($game);

    expect($loaded->playthroughs->map->completed_at->map->toDateString()->all())->toBe(['2025-01-01', '2024-01-01']);
});

test('create defaults to the backlog when no status is given', function () {
    $game = $this->service->create($this->user, ['title' => 'Hades', 'status' => null]);

    expect($game->user->is($this->user))->toBeTrue()
        ->and($game->fresh()->status)->toBe(GameStatus::Backlog);
});

test('create uses the given status', function () {
    $game = $this->service->create($this->user, ['title' => 'Hades', 'status' => 'playing']);

    expect($game->fresh()->status)->toBe(GameStatus::Playing);
});

test('update keeps the current status when none is given', function () {
    $game = Game::factory()->completed()->create();

    $this->service->update($game, ['title' => 'Novo título', 'status' => null]);

    expect($game->fresh())
        ->title->toBe('Novo título')
        ->status->toBe(GameStatus::Completed);
});

test('delete removes the game and its playthroughs', function () {
    $game = Game::factory()->completed()->has(Playthrough::factory())->create();

    $this->service->delete($game);

    expect(Game::count())->toBe(0)->and(Playthrough::count())->toBe(0);
});

describe('covers', function () {
    beforeEach(function () {
        config(['services.rawg.key' => 'test-key']);
    });

    test('create stores the cover and the RAWG id', function () {
        $game = $this->service->create($this->user, [
            'title' => 'Hades',
            'rawg_id' => 123,
            'cover_url' => 'https://media.rawg.io/hades.jpg',
        ]);

        expect($game->fresh())
            ->rawg_id->toBe(123)
            ->cover_url->toBe('https://media.rawg.io/hades.jpg');
    });

    test('create accepts a manual cover without a RAWG id', function () {
        $game = $this->service->create($this->user, ['title' => 'Hades', 'rawg_id' => null, 'cover_url' => 'https://exemplo.com/hades.png']);

        expect($game->fresh())
            ->rawg_id->toBeNull()
            ->cover_url->toBe('https://exemplo.com/hades.png');
    });

    test('update removes the cover and the RAWG id when cover_url is null', function () {
        $game = Game::factory()->withCover()->create();

        $this->service->update($game, ['title' => $game->title, 'rawg_id' => 99, 'cover_url' => null]);

        expect($game->fresh())->rawg_id->toBeNull()->cover_url->toBeNull();
    });

    test('update keeps the cover when the cover fields are not given', function () {
        $game = Game::factory()->withCover()->create();
        $cover = $game->cover_url;

        $this->service->update($game, ['title' => 'Novo título']);

        expect($game->fresh())->title->toBe('Novo título')->cover_url->toBe($cover);
    });

    test('attachCoverByTitle links the game when a result has the exact title', function () {
        Http::fake(['api.rawg.io/*' => Http::response(['results' => [
            ['id' => 1, 'name' => 'Hades II', 'background_image' => 'https://media.rawg.io/hades2.jpg'],
            ['id' => 2, 'name' => 'hades', 'background_image' => 'https://media.rawg.io/hades.jpg'],
        ]])]);
        $game = Game::factory()->create(['title' => 'Hades']);

        expect($this->service->attachCoverByTitle($game))->toBeTrue()
            ->and($game->fresh())
            ->rawg_id->toBe(2)
            ->cover_url->toBe('https://media.rawg.io/hades.jpg');
    });

    test('attachCoverByTitle leaves ambiguous titles alone', function () {
        Http::fake(['api.rawg.io/*' => Http::response(['results' => [
            ['id' => 1, 'name' => 'Hades II', 'background_image' => 'https://media.rawg.io/hades2.jpg'],
        ]])]);
        $game = Game::factory()->create(['title' => 'Hades']);

        expect($this->service->attachCoverByTitle($game))->toBeFalse()
            ->and($game->fresh()->cover_url)->toBeNull();
    });

    test('attachCoverByTitle never overwrites an existing cover', function () {
        Http::fake();
        $game = Game::factory()->create(['title' => 'Hades', 'cover_url' => 'https://exemplo.com/manual.png']);

        expect($this->service->attachCoverByTitle($game))->toBeFalse();

        Http::assertNothingSent();
    });

    test('withoutCover lists only games without a cover', function () {
        Game::factory()->withCover()->create();
        $semCapa = Game::factory()->create();

        expect($this->service->withoutCover()->pluck('id')->all())->toBe([$semCapa->id]);
    });
});

test('paginate flags the games that have a platinum playthrough', function () {
    Game::factory()->for($this->user)->completed()
        ->has(Playthrough::factory()->state(['completion_level' => 'story']))
        ->has(Playthrough::factory()->state(['completion_level' => 'platinum']))
        ->create(['title' => 'Com platina']);
    Game::factory()->for($this->user)->completed()
        ->has(Playthrough::factory()->state(['completion_level' => 'full']))
        ->create(['title' => 'Sem platina']);
    Game::factory()->for($this->user)->create(['title' => 'Backlog']);

    $games = $this->service->paginate($this->user)->keyBy('title');

    expect($games['Com platina']->has_platinum)->toBeTrue()
        ->and($games['Sem platina']->has_platinum)->toBeFalse()
        ->and($games['Backlog']->has_platinum)->toBeFalse();
});

test('paginate returns ten games per page by default', function () {
    Game::factory()->for($this->user)->count(11)->sequence(fn ($sequence) => ['title' => sprintf('Jogo %02d', $sequence->index)])->create();

    $games = $this->service->paginate($this->user);

    expect($games->perPage())->toBe(10)
        ->and($games->items())->toHaveCount(10)
        ->and($games->total())->toBe(11);
});

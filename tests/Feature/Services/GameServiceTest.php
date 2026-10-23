<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\GameService;

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

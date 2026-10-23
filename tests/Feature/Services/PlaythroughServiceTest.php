<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\PlaythroughService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(PlaythroughService::class);
    $this->attributes = [
        'completed_at' => '2026-03-15',
        'platform' => 'switch',
        'rating' => 5,
        'completion_level' => 'full',
    ];
});

describe('register', function () {
    test('creates the game and marks it as completed when the user does not have it', function () {
        $playthrough = $this->service->register($this->user, 'Hollow Knight', $this->attributes);

        $game = $this->user->games()->sole();

        expect($game->title)->toBe('Hollow Knight')
            ->and($game->status)->toBe(GameStatus::Completed)
            ->and($playthrough->game->is($game))->toBeTrue()
            ->and($playthrough->rating)->toBe(5);
    });

    test('reuses the game of the user instead of duplicating it', function () {
        $game = Game::factory()->for($this->user)->create(['title' => 'Hollow Knight']);

        $this->service->register($this->user, 'Hollow Knight', $this->attributes);

        expect($this->user->games()->count())->toBe(1)
            ->and($game->fresh()->status)->toBe(GameStatus::Completed);
    });

    test('adds another playthrough to a game that was already completed', function () {
        $game = Game::factory()->completed()->for($this->user)->has(Playthrough::factory())->create(['title' => 'Hollow Knight']);

        $this->service->register($this->user, 'Hollow Knight', $this->attributes);

        expect($game->playthroughs()->count())->toBe(2);
    });

    test('does not touch a game of another user with the same title', function () {
        $other = Game::factory()->create(['title' => 'Hollow Knight']);

        $this->service->register($this->user, 'Hollow Knight', $this->attributes);

        expect($other->playthroughs()->count())->toBe(0)
            ->and($other->fresh()->status)->toBe(GameStatus::Backlog);
    });
});

describe('update', function () {
    test('changes the playthrough attributes', function () {
        $playthrough = Playthrough::factory()->create();

        $this->service->update($playthrough, [...$this->attributes, 'rating' => 2]);

        expect($playthrough->fresh())
            ->rating->toBe(2)
            ->completed_at->toDateString()->toBe('2026-03-15');
    });
});

describe('remove', function () {
    test('sends the game back to the backlog when its last playthrough is removed', function () {
        $game = Game::factory()->completed()->create();
        $playthrough = Playthrough::factory()->for($game)->create();

        $this->service->remove($playthrough);

        expect(Playthrough::count())->toBe(0)
            ->and($game->fresh()->status)->toBe(GameStatus::Backlog);
    });

    test('keeps the game completed while other playthroughs remain', function () {
        $game = Game::factory()->completed()->create();
        [$first] = Playthrough::factory()->for($game)->count(2)->create();

        $this->service->remove($first);

        expect($game->fresh()->status)->toBe(GameStatus::Completed);
    });

    test('does not change a game that is being replayed', function () {
        $game = Game::factory()->playing()->create();
        $playthrough = Playthrough::factory()->for($game)->create();

        $this->service->remove($playthrough);

        expect($game->fresh()->status)->toBe(GameStatus::Playing);
    });
});

describe('paginate', function () {
    test('returns only the playthroughs of the user, newest first', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->create(['completed_at' => '2024-01-10']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2025-06-20']);
        Playthrough::factory()->create();

        $dates = $this->service->paginate($this->user)->getCollection()->map->completed_at->map->toDateString()->all();

        expect($dates)->toBe(['2025-06-20', '2024-01-10']);
    });

    test('filters by year including its first and last day', function () {
        $game = Game::factory()->completed()->for($this->user)->create();

        foreach (['2024-12-31', '2025-01-01', '2025-12-31', '2026-01-01'] as $date) {
            Playthrough::factory()->for($game)->create(['completed_at' => $date]);
        }

        $dates = $this->service->paginate($this->user, 2025)->getCollection()->map->completed_at->map->toDateString()->all();

        expect($dates)->toBe(['2025-12-31', '2025-01-01']);
    });
});

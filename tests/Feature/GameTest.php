<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('a game belongs to a user and defaults to backlog', function () {
    $game = Game::factory()->create();

    expect($game->user)->toBeInstanceOf(User::class)
        ->and($game->status)->toBe(GameStatus::Backlog);
});

test('a game can have many playthroughs', function () {
    $game = Game::factory()->completed()->has(Playthrough::factory()->count(2))->create();

    expect($game->playthroughs)->toHaveCount(2);
});

test('a user cannot have two games with the same title', function () {
    $user = User::factory()->create();
    Game::factory()->for($user)->create(['title' => 'Hollow Knight']);

    Game::factory()->for($user)->create(['title' => 'Hollow Knight']);
})->throws(UniqueConstraintViolationException::class);

test('different users can have games with the same title', function () {
    Game::factory()->count(2)->state(['title' => 'Hollow Knight'])->create();

    expect(Game::where('title', 'Hollow Knight')->count())->toBe(2);
});

test('deleting a game deletes its playthroughs', function () {
    $game = Game::factory()->completed()->has(Playthrough::factory())->create();

    $game->delete();

    expect(Playthrough::count())->toBe(0);
});

test('every game status has a label', function (GameStatus $status) {
    expect($status->label())->toBeString()->not->toBeEmpty();
})->with(GameStatus::cases());

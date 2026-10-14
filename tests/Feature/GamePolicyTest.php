<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;

test('the owner can manage their game', function () {
    $game = Game::factory()->create();

    expect($game->user->can('view', $game))->toBeTrue()
        ->and($game->user->can('update', $game))->toBeTrue()
        ->and($game->user->can('delete', $game))->toBeTrue();
});

test('another user cannot manage the game', function () {
    $game = Game::factory()->create();
    $stranger = User::factory()->create();

    expect($stranger->can('view', $game))->toBeFalse()
        ->and($stranger->can('update', $game))->toBeFalse()
        ->and($stranger->can('delete', $game))->toBeFalse();
});

test('only the game owner can manage its playthroughs', function () {
    $playthrough = Playthrough::factory()->create();
    $stranger = User::factory()->create();

    expect($playthrough->game->user->can('update', $playthrough))->toBeTrue()
        ->and($playthrough->game->user->can('delete', $playthrough))->toBeTrue()
        ->and($stranger->can('update', $playthrough))->toBeFalse()
        ->and($stranger->can('delete', $playthrough))->toBeFalse();
});

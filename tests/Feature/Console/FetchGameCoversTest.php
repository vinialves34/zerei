<?php

use App\Models\Game;
use Illuminate\Support\Facades\Http;

test('links games with an exact title match and lists the pending ones', function () {
    config(['services.rawg.key' => 'test-key']);

    Http::fake([
        'api.rawg.io/*' => fn ($request) => Http::response(['results' => $request['search'] === 'celeste'
            ? [['id' => 5, 'name' => 'Celeste', 'background_image' => 'https://media.rawg.io/celeste.jpg']]
            : [['id' => 6, 'name' => 'Outro jogo', 'background_image' => 'https://media.rawg.io/outro.jpg']]]),
    ]);

    $celeste = Game::factory()->create(['title' => 'Celeste']);
    $obscuro = Game::factory()->create(['title' => 'Jogo Obscuro']);
    $manual = Game::factory()->create(['title' => 'Manual', 'cover_url' => 'https://exemplo.com/manual.png']);

    $this->artisan('games:fetch-covers', ['--delay' => 0])
        ->expectsOutputToContain('1 jogo(s) vinculado(s)')
        ->expectsOutputToContain('Jogo Obscuro')
        ->assertSuccessful();

    expect($celeste->fresh())->rawg_id->toBe(5)->cover_url->toBe('https://media.rawg.io/celeste.jpg')
        ->and($obscuro->fresh()->cover_url)->toBeNull()
        ->and($manual->fresh()->cover_url)->toBe('https://exemplo.com/manual.png');
});

test('fails without the RAWG key', function () {
    config(['services.rawg.key' => null]);
    Http::fake();

    $this->artisan('games:fetch-covers')->assertFailed();

    Http::assertNothingSent();
});

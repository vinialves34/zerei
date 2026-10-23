<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests are redirected to the login screen', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect(route('login'));
})->with([
    'index' => ['GET', '/games'],
    'create' => ['GET', '/games/create'],
    'store' => ['POST', '/games'],
    'show' => ['GET', '/games/1'],
    'edit' => ['GET', '/games/1/edit'],
    'update' => ['PUT', '/games/1'],
    'destroy' => ['DELETE', '/games/1'],
]);

describe('index', function () {
    test('lists only the games of the authenticated user ordered by title', function () {
        Game::factory()->for($this->user)->create(['title' => 'Zelda']);
        Game::factory()->for($this->user)->create(['title' => 'Celeste']);
        Game::factory()->create(['title' => 'Jogo de outro usuário']);

        $this->actingAs($this->user)
            ->get(route('games.index'))
            ->assertOk()
            ->assertSeeInOrder(['Celeste', 'Zelda'])
            ->assertDontSee('Jogo de outro usuário');
    });

    test('filters games by status', function () {
        Game::factory()->for($this->user)->create(['title' => 'No backlog']);
        Game::factory()->for($this->user)->playing()->create(['title' => 'Em andamento']);

        $this->actingAs($this->user)
            ->get(route('games.index', ['status' => 'playing']))
            ->assertOk()
            ->assertSee('Em andamento')
            ->assertDontSee('No backlog');
    });

    test('ignores an unknown status filter', function () {
        Game::factory()->for($this->user)->create(['title' => 'No backlog']);

        $this->actingAs($this->user)
            ->get(route('games.index', ['status' => 'inexistente']))
            ->assertOk()
            ->assertSee('No backlog');
    });
});

describe('store', function () {
    test('creates a backlog game for the authenticated user by default', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hollow Knight'])
            ->assertRedirect();

        $game = $this->user->games()->sole();

        expect($game->title)->toBe('Hollow Knight')
            ->and($game->status)->toBe(GameStatus::Backlog);
    });

    test('creates a game with the chosen status', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades', 'status' => 'playing']);

        expect($this->user->games()->sole()->status)->toBe(GameStatus::Playing);
    });

    test('rejects the completed status because it comes from registering a playthrough', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades', 'status' => 'completed'])
            ->assertSessionHasErrors(['status' => 'O valor selecionado para status é inválido.']);

        expect(Game::count())->toBe(0);
    });

    test('requires a title', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), [])
            ->assertSessionHasErrors(['title' => 'O campo título é obrigatório.']);
    });

    test('rejects a title the user already has', function () {
        Game::factory()->for($this->user)->create(['title' => 'Hades']);

        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades'])
            ->assertSessionHasErrors(['title' => 'Você já cadastrou um jogo com este título.']);

        expect($this->user->games()->count())->toBe(1);
    });

    test('accepts a title that only another user has', function () {
        Game::factory()->create(['title' => 'Hades']);

        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades'])
            ->assertSessionHasNoErrors();

        expect($this->user->games()->count())->toBe(1);
    });
});

describe('show', function () {
    test('lists the playthroughs of the game', function () {
        $game = Game::factory()->completed()->for($this->user)
            ->has(Playthrough::factory()->state(['completed_at' => '2025-03-10']))
            ->create();

        $this->actingAs($this->user)
            ->get(route('games.show', $game))
            ->assertOk()
            ->assertSee('10/03/2025');
    });

    test('returns 404 for a game of another user', function () {
        $game = Game::factory()->create();

        $this->actingAs($this->user)
            ->get(route('games.show', $game))
            ->assertNotFound();
    });
});

describe('update', function () {
    test('changes the title and status', function () {
        $game = Game::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => 'Novo título', 'status' => 'dropped'])
            ->assertRedirect(route('games.show', $game));

        expect($game->fresh())
            ->title->toBe('Novo título')
            ->status->toBe(GameStatus::Dropped);
    });

    test('keeps the completed status when the status is left blank', function () {
        $game = Game::factory()->completed()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => 'Outro nome', 'status' => '']);

        expect($game->fresh())
            ->title->toBe('Outro nome')
            ->status->toBe(GameStatus::Completed);
    });

    test('moves a completed game back to playing so it can be replayed', function () {
        $game = Game::factory()->completed()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => $game->title, 'status' => 'playing']);

        expect($game->fresh()->status)->toBe(GameStatus::Playing);
    });

    test('allows saving a game without changing its own title', function () {
        $game = Game::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => $game->title])
            ->assertSessionHasNoErrors();
    });

    test('rejects a title that another game of the user already has', function () {
        Game::factory()->for($this->user)->create(['title' => 'Hades']);
        $game = Game::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => 'Hades'])
            ->assertSessionHasErrors('title');
    });

    test('returns 404 for a game of another user', function () {
        $game = Game::factory()->create(['title' => 'Original']);

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => 'Alterado'])
            ->assertNotFound();

        expect($game->fresh()->title)->toBe('Original');
    });
});

describe('destroy', function () {
    test('deletes the game and its playthroughs', function () {
        $game = Game::factory()->completed()->for($this->user)->has(Playthrough::factory())->create();

        $this->actingAs($this->user)
            ->delete(route('games.destroy', $game))
            ->assertRedirect(route('games.index'));

        expect(Game::count())->toBe(0)
            ->and(Playthrough::count())->toBe(0);
    });

    test('returns 404 for a game of another user', function () {
        $game = Game::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('games.destroy', $game))
            ->assertNotFound();

        expect(Game::count())->toBe(1);
    });
});

describe('edit', function () {
    test('renders the form for the owner', function () {
        $game = Game::factory()->for($this->user)->create(['title' => 'Hades']);

        $this->actingAs($this->user)
            ->get(route('games.edit', $game))
            ->assertOk()
            ->assertSee('Hades');
    });

    test('returns 404 for a game of another user', function () {
        $this->actingAs($this->user)
            ->get(route('games.edit', Game::factory()->create()))
            ->assertNotFound();
    });
});

describe('covers', function () {
    test('stores the chosen RAWG cover on create', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades', 'rawg_id' => 123, 'cover_url' => 'https://media.rawg.io/hades.jpg'])
            ->assertSessionHasNoErrors();

        expect($this->user->games()->sole())
            ->rawg_id->toBe(123)
            ->cover_url->toBe('https://media.rawg.io/hades.jpg');
    });

    test('accepts a manual cover link on update and removes it when emptied', function () {
        $game = Game::factory()->for($this->user)->withCover()->create();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => $game->title, 'cover_url' => 'https://exemplo.com/capa.png', 'rawg_id' => ''])
            ->assertSessionHasNoErrors();

        expect($game->fresh())->cover_url->toBe('https://exemplo.com/capa.png')->rawg_id->toBeNull();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), ['title' => $game->title, 'cover_url' => '', 'rawg_id' => ''])
            ->assertSessionHasNoErrors();

        expect($game->fresh()->cover_url)->toBeNull();
    });

    test('rejects a cover link that is not https', function (string $url) {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades', 'cover_url' => $url])
            ->assertSessionHasErrors('cover_url');

        expect(Game::count())->toBe(0);
    })->with([
        'http' => 'http://exemplo.com/capa.png',
        'javascript' => 'javascript:alert(1)',
        'data' => 'data:image/png;base64,AAAA',
        'text' => 'não é um link',
    ]);

    test('rejects an invalid RAWG id', function () {
        $this->actingAs($this->user)
            ->post(route('games.store'), ['title' => 'Hades', 'rawg_id' => 'abc'])
            ->assertSessionHasErrors('rawg_id');
    });

    test('shows the cover in the list and the RAWG attribution', function () {
        Game::factory()->for($this->user)->create(['title' => 'Hades', 'cover_url' => 'https://media.rawg.io/hades.jpg']);

        $this->actingAs($this->user)
            ->get(route('games.index'))
            ->assertOk()
            ->assertSee('https://media.rawg.io/hades.jpg', false)
            ->assertSee('https://rawg.io', false);
    });

    test('shows the initials when the game has no cover', function () {
        Game::factory()->for($this->user)->create(['title' => 'Hollow Knight']);

        $this->actingAs($this->user)
            ->get(route('games.index'))
            ->assertOk()
            ->assertDontSee('<img', false);
    });
});

describe('rawg search', function () {
    beforeEach(function () {
        config(['services.rawg.key' => 'test-key']);
    });

    test('guests are redirected to the login screen', function () {
        $this->get(route('games.rawg-search', ['q' => 'hades']))->assertRedirect(route('login'));
    });

    test('returns the RAWG results as JSON', function () {
        Http::fake(['api.rawg.io/*' => Http::response(['results' => [
            ['id' => 7, 'name' => 'Hades', 'released' => '2020-09-17', 'background_image' => 'https://media.rawg.io/hades.jpg'],
        ]])]);

        $this->actingAs($this->user)
            ->getJson(route('games.rawg-search', ['q' => 'hades']))
            ->assertOk()
            ->assertExactJson([
                ['id' => 7, 'name' => 'Hades', 'released' => '2020-09-17', 'cover_url' => 'https://media.rawg.io/hades.jpg'],
            ]);
    });

    test('requires a query with at least two characters', function (?string $query) {
        Http::fake();

        $this->actingAs($this->user)
            ->getJson(route('games.rawg-search', ['q' => $query]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');

        Http::assertNothingSent();
    })->with([null, 'a']);

    test('returns an empty list when RAWG is down', function () {
        Http::fake(['api.rawg.io/*' => Http::response(null, 500)]);

        $this->actingAs($this->user)
            ->getJson(route('games.rawg-search', ['q' => 'hades']))
            ->assertOk()
            ->assertExactJson([]);
    });
});

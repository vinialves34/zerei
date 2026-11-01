<?php

use App\Enums\CompletionLevel;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('profile page shows the level card with the points of the user', function () {
    $user = User::factory()->create();
    $game = Game::factory()->completed()->for($user)->create();
    Playthrough::factory()->for($game)->create([
        'completed_at' => now(),
        'completion_level' => CompletionLevel::Platinum,
    ]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Nível 2')
        ->assertSee('200 pontos')
        ->assertSee('Pontos de '.now()->year)
        ->assertSee('Faltam 100 para o nível 3');
});

test('the sidebar shows the level next to the user name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Nv. 1 · Novato');
});

test('profile page lists the achievements and marks the unlocked ones', function () {
    $user = User::factory()->create();
    $game = Game::factory()->completed()->for($user)->create();
    Playthrough::factory()->for($game)->create(['completion_level' => CompletionLevel::Platinum]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Conquistas')
        ->assertSee('1 de 4 desbloqueadas')
        ->assertSee('Primeira platina')
        ->assertSee('5 plataformas diferentes');
});

test('the level up notice appears once after the user goes up a level', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('data-level-up', false);

    $game = Game::factory()->completed()->for($user)->create();
    Playthrough::factory()->for($game)->create(['completion_level' => CompletionLevel::Platinum]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Subiu de nível!')
        ->assertSee('nível 2 · Novato');

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Subiu de nível!');
});

test('the level up notice is not shown to a user whose level was never recorded and did not change', function () {
    $user = User::factory()->create();
    $game = Game::factory()->completed()->for($user)->create();
    Playthrough::factory()->for($game)->create(['completion_level' => CompletionLevel::Platinum]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Subiu de nível!');
    expect($user->fresh()->last_seen_level)->toBe(2);
});

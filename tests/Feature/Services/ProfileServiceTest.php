<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\ProfileService;

beforeEach(function () {
    $this->service = app(ProfileService::class);
});

describe('update', function () {
    test('changes the name and email and requires the new email to be verified again', function () {
        $user = User::factory()->create();

        $this->service->update($user, ['name' => 'Novo Nome', 'email' => 'novo@example.com']);

        expect($user->fresh())
            ->name->toBe('Novo Nome')
            ->email->toBe('novo@example.com')
            ->email_verified_at->toBeNull();
    });

    test('keeps the email verified when the email does not change', function () {
        $user = User::factory()->create();

        $this->service->update($user, ['name' => 'Novo Nome', 'email' => $user->email]);

        expect($user->fresh())
            ->name->toBe('Novo Nome')
            ->email_verified_at->not->toBeNull();
    });
});

describe('delete', function () {
    test('removes the account with its games and playthroughs and keeps other users data', function () {
        $user = User::factory()->create();
        Game::factory()->for($user)->completed()->has(Playthrough::factory())->create();
        $other = Game::factory()->completed()->has(Playthrough::factory())->create();

        $this->service->delete($user);

        expect(User::find($user->id))->toBeNull()
            ->and(Game::where('user_id', $user->id)->count())->toBe(0)
            ->and(Game::find($other->id))->not->toBeNull()
            ->and(Playthrough::count())->toBe(1);
    });
});

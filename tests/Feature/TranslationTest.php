<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

test('the application locale is brazilian portuguese', function () {
    expect(app()->getLocale())->toBe('pt_BR');
});

test('every literal translation string in views and app code is translated', function () {
    $translations = json_decode(File::get(lang_path('pt_BR.json')), true, flags: JSON_THROW_ON_ERROR);

    $files = [
        ...File::allFiles(resource_path('views')),
        ...File::allFiles(app_path()),
    ];

    $missing = collect($files)
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all('/__\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/', File::get($file->getPathname()), $matches);

            return array_map(
                fn (string $single, string $double): string => stripslashes($single !== '' ? $single : $double),
                $matches[1],
                $matches[2],
            );
        })
        ->reject(fn (string $key): bool => str_contains($key, '.') && ! str_contains($key, ' '))
        ->unique()
        ->reject(fn (string $key): bool => array_key_exists($key, $translations))
        ->values()
        ->all();

    expect($missing)->toBe([]);
});

test('validation messages are shown in portuguese with translated attribute names', function () {
    $validator = Validator::make(['email' => ''], ['email' => 'required']);

    expect($validator->errors()->first('email'))->toBe('O campo e-mail é obrigatório.');
});

test('the login screen is rendered in portuguese', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Entrar')
        ->assertSee('Esqueceu sua senha?')
        ->assertSee('lang="pt-BR"', false);
});

test('authentication failures are shown in portuguese', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'Essas credenciais não correspondem aos nossos registros.']);
});

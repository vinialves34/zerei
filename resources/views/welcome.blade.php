<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        @include('layouts.head')
    </head>
    <body class="min-h-screen bg-app font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-4 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between py-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-lg font-semibold">
                    <x-application-logo />
                    {{ config('app.name') }}
                </a>

                <div class="flex items-center gap-2">
                    <x-theme-toggle />
                    @auth
                        <x-button :href="route('dashboard')">Ir para o painel</x-button>
                    @else
                        <x-button :href="route('login')" variant="ghost">{{ __('Log in') }}</x-button>
                        <x-button :href="route('register')" class="max-sm:hidden">Criar conta</x-button>
                    @endauth
                </div>
            </header>

            <main class="flex flex-1 flex-col justify-center py-12">
                <p class="text-sm font-medium text-accent">Seu diário de jogos zerados</p>
                <h1 class="mt-3 max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl">
                    Registre tudo o que você zerou e veja sua jornada ano a ano.
                </h1>
                <p class="mt-4 max-w-xl text-lg text-muted">
                    Cadastre cada zerada com data, plataforma e nota, e mantenha uma lista dos jogos que ainda quer zerar.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <x-button :href="route('dashboard')" class="px-6 py-3">Ir para o painel</x-button>
                    @else
                        <x-button :href="route('register')" class="px-6 py-3">Criar conta</x-button>
                        <x-button :href="route('login')" variant="secondary" class="px-6 py-3">{{ __('Log in') }}</x-button>
                    @endauth
                </div>

                <div class="mt-16 grid gap-4 sm:grid-cols-3">
                    <x-card class="p-5">
                        <x-icon name="trophy" class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium">Zeradas e rejogadas</h2>
                        <p class="mt-1 text-sm text-muted">Data, plataforma, nota e nível de conclusão: história, 100% ou platina.</p>
                    </x-card>
                    <x-card class="p-5">
                        <x-icon name="bookmark" class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium">Backlog</h2>
                        <p class="mt-1 text-sm text-muted">Guarde os jogos que quer zerar e acompanhe o que está jogando agora.</p>
                    </x-card>
                    <x-card class="p-5">
                        <x-icon name="calendar" class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium">Linha do tempo</h2>
                        <p class="mt-1 text-sm text-muted">Quantos jogos você zerou em cada ano, mês a mês.</p>
                    </x-card>
                </div>
            </main>
        </div>
    </body>
</html>

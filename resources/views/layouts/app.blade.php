<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        @include('layouts.head')
    </head>
    <body class="min-h-screen bg-app font-sans text-ink antialiased">
        @include('layouts.navigation')

        <div class="lg:pl-60">
            <main class="mx-auto w-full max-w-5xl px-4 pb-8 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-10">
                @if ($title)
                    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>
                            @endif
                        </div>

                        @isset($actions)
                            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                        @endisset
                    </header>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-ok/30 bg-ok/10 px-4 py-3 text-sm text-ok" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="mx-auto w-full max-w-5xl px-4 pb-28 text-xs text-muted sm:px-6 lg:px-8 lg:pb-8">
                Capas de jogos por <a href="https://rawg.io" target="_blank" rel="noopener" class="underline transition hover:text-ink">RAWG</a>
            </footer>
        </div>
    </body>
</html>

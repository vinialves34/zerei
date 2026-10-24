<x-app-layout title="Painel" :subtitle="'Seu progresso em '.$year">
    <x-slot name="actions">
        <x-button :href="route('playthroughs.create')" class="max-lg:hidden">
            <x-icon name="plus" class="size-4" />
            Registrar zerada
        </x-button>
    </x-slot>

    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('years.show', $year) }}" class="rounded-xl border border-line bg-surface p-3 transition hover:border-muted sm:p-4">
            <div class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="trophy" class="size-4 max-sm:hidden" />Zeradas</div>
            <div class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $completedThisYear }}</div>
            <div class="text-xs text-muted">em {{ $year }}</div>
        </a>
        <a href="{{ route('games.index', ['status' => 'backlog']) }}" class="rounded-xl border border-line bg-surface p-3 transition hover:border-muted sm:p-4">
            <div class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="bookmark" class="size-4 max-sm:hidden" />Para zerar</div>
            <div class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $backlogCount }}</div>
            <div class="text-xs text-muted">no backlog</div>
        </a>
        <a href="{{ route('games.index', ['status' => 'playing']) }}" class="rounded-xl border border-line bg-surface p-3 transition hover:border-muted sm:p-4">
            <div class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="play" class="size-4 max-sm:hidden" />Jogando</div>
            <div class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $playingGames->count() }}</div>
            <div class="text-xs text-muted">em andamento</div>
        </a>
    </div>

    <x-card class="mt-4 p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-medium text-muted">Zeradas por mês</h2>
            <a href="{{ route('years.show', $year) }}" class="text-sm text-accent hover:underline">{{ $year }}</a>
        </div>
        <x-month-chart :counts="$monthlyCounts" :highlight="now()->month" />
    </x-card>

    <section class="mt-8">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Últimas zeradas</h2>
            <a href="{{ route('playthroughs.index') }}" class="text-sm text-accent hover:underline">Ver todas</a>
        </div>

        @if ($recentPlaythroughs->isEmpty())
            <x-card>
                <x-empty-state title="Registre sua primeira zerada" description="Cada jogo que você terminar aparece aqui.">
                    <x-button :href="route('playthroughs.create')">Registrar zerada</x-button>
                </x-empty-state>
            </x-card>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                @foreach ($recentPlaythroughs as $playthrough)
                    <a href="{{ route('games.show', $playthrough->game) }}" class="group block min-w-0">
                        <div class="relative">
                            <x-game-cover :title="$playthrough->game->title" :url="$playthrough->game->cover_url" class="transition group-hover:brightness-110" />
                            @if ($playthrough->completion_level === \App\Enums\CompletionLevel::Platinum)
                                <x-platinum-badge overlay class="absolute right-2 top-2" />
                            @endif
                        </div>
                        <div class="mt-2 truncate text-sm font-medium">{{ $playthrough->game->title }}</div>
                        <div class="mt-0.5 flex items-center justify-between gap-2 text-xs">
                            <x-pill>{{ $playthrough->platform->label() }}</x-pill>
                            <x-rating :value="$playthrough->rating" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-8">
        <h2 class="mb-3 text-lg font-semibold">Jogando agora</h2>
        <x-card class="divide-y divide-line">
            @forelse ($playingGames as $game)
                <a href="{{ route('games.show', $game) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-surface-2/50">
                    <span class="size-2 shrink-0 rounded-full bg-ok" aria-hidden="true"></span>
                    <span class="min-w-0 flex-1 truncate">{{ $game->title }}</span>
                    <x-icon name="chevron-right" class="size-4 text-muted" />
                </a>
            @empty
                <x-empty-state title="Nenhum jogo em andamento" description="Mude o status de um jogo do backlog para Jogando." class="py-8" />
            @endforelse
        </x-card>
    </section>
</x-app-layout>

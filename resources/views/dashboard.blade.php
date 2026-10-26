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

    <x-card class="mt-4 p-4 sm:p-5" x-data="{ editing: {{ $errors->hasAny(['target', 'year']) ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-medium text-muted">Meta de {{ $year }}</h2>
            @if ($goalProgress)
                <button type="button" class="inline-flex size-8 items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink" x-show="! editing" x-on:click="editing = true" aria-label="Editar meta de {{ $year }}">
                    <x-icon name="pencil" class="size-4" />
                </button>
            @endif
        </div>

        <div x-show="! editing">
            @if ($goalProgress)
                <x-goal-progress :progress="$goalProgress" :year="$year" class="mt-3" />
            @else
                <p class="mt-2 text-sm text-muted">Defina quantos jogos você quer zerar em {{ $year }} e acompanhe o progresso aqui.</p>
                <x-button type="button" variant="secondary" class="mt-3" x-on:click="editing = true">Definir meta</x-button>
            @endif
        </div>

        <div x-show="editing" x-cloak class="mt-3">
            <form method="POST" action="{{ route('goals.update', $year) }}">
                @csrf
                @method('PUT')
                <x-input-label for="goal-target" value="Quantos jogos você quer zerar em {{ $year }}?" />
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <x-text-input id="goal-target" name="target" type="number" min="1" max="999" inputmode="numeric" class="w-28" :value="old('target', $goalProgress['target'] ?? '')" required />
                    <x-button>Salvar</x-button>
                    <x-button type="button" variant="ghost" x-on:click="editing = false">Cancelar</x-button>
                </div>
                <x-input-error :messages="$errors->get('target')" class="mt-2" />
                <x-input-error :messages="$errors->get('year')" class="mt-2" />
            </form>

            @if ($goalProgress)
                <form method="POST" action="{{ route('goals.destroy', $year) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-danger hover:underline">Remover meta</button>
                </form>
            @endif
        </div>
    </x-card>

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

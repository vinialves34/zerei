<x-app-layout>
    <a href="{{ route('games.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-ink">
        <x-icon name="arrow-left" class="size-4" />
        Jogos
    </a>

    <div class="flex items-start gap-4 sm:gap-6">
        <x-game-cover :title="$game->title" :url="$game->cover_url" size="lg" />

        <div class="min-w-0 flex-1">
            <x-status-badge :status="$game->status" />
            <h1 class="mt-2 break-words text-2xl font-semibold tracking-tight sm:text-3xl">{{ $game->title }}</h1>
            <p class="mt-1 text-sm text-muted">
                {{ $game->playthroughs->count() }} {{ $game->playthroughs->count() === 1 ? 'zerada' : 'zeradas' }}
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <x-button :href="route('playthroughs.create', ['game' => $game])">
                    <x-icon name="plus" class="size-4" />
                    Registrar zerada
                </x-button>
                <x-button :href="route('games.edit', $game)" variant="secondary">
                    <x-icon name="pencil" class="size-4" />
                    Editar
                </x-button>
            </div>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="mb-3 text-lg font-semibold">Zeradas</h2>
        <x-card class="divide-y divide-line">
            @forelse ($game->playthroughs as $playthrough)
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3">
                    <div class="min-w-0">
                        <div class="font-medium">{{ $playthrough->completed_at->format('d/m/Y') }}</div>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <x-pill>{{ $playthrough->platform->label() }}</x-pill>
                            <x-pill>{{ $playthrough->completion_level->label() }}</x-pill>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-rating :value="$playthrough->rating" />
                        <a href="{{ route('playthroughs.edit', $playthrough) }}" class="inline-flex size-9 items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink" aria-label="Editar zerada">
                            <x-icon name="pencil" class="size-4" />
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state title="Este jogo ainda não foi zerado" description="Registre a zerada quando terminar." class="py-8" />
            @endforelse
        </x-card>
    </section>

    <section class="mt-10 border-t border-line pt-6">
        <form method="POST" action="{{ route('games.destroy', $game) }}" onsubmit="return confirm('Remover este jogo e todas as suas zeradas?')">
            @csrf
            @method('DELETE')
            <x-button variant="ghost" class="text-danger hover:text-danger">
                <x-icon name="trash" class="size-4" />
                Remover jogo
            </x-button>
        </form>
    </section>
</x-app-layout>

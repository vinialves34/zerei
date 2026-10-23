<x-app-layout :title="$year ? 'Zeradas de '.$year : 'Zeradas'" subtitle="Tudo o que você terminou">
    <x-slot name="actions">
        <x-button :href="route('playthroughs.create')" class="max-lg:hidden">
            <x-icon name="plus" class="size-4" />
            Registrar zerada
        </x-button>
    </x-slot>

    <nav class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Filtrar por ano">
        <a href="{{ route('playthroughs.index') }}" @class(['shrink-0 rounded-full px-3.5 py-1.5 text-sm transition', 'bg-accent text-accent-ink' => ! $year, 'bg-surface-2 text-muted hover:text-ink' => $year])>Todos os anos</a>
        @foreach ($years as $option)
            <a href="{{ route('playthroughs.index', ['year' => $option]) }}" @class(['shrink-0 rounded-full px-3.5 py-1.5 text-sm transition', 'bg-accent text-accent-ink' => $year === $option, 'bg-surface-2 text-muted hover:text-ink' => $year !== $option])>{{ $option }}</a>
        @endforeach
    </nav>

    <x-card class="divide-y divide-line">
        @forelse ($playthroughs as $playthrough)
            <div class="flex items-center gap-3 px-4 py-3">
                <a href="{{ route('games.show', $playthrough->game) }}" class="flex min-w-0 flex-1 items-center gap-3">
                    <x-game-cover :title="$playthrough->game->title" :url="$playthrough->game->cover_url" size="sm" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">{{ $playthrough->game->title }}</div>
                        <div class="truncate text-xs text-muted">
                            {{ $playthrough->completed_at->format('d/m/Y') }} · {{ $playthrough->platform->label() }} · {{ $playthrough->completion_level->label() }}
                        </div>
                    </div>
                    <x-rating :value="$playthrough->rating" class="hidden text-sm sm:inline-flex" />
                </a>
                <a href="{{ route('playthroughs.edit', $playthrough) }}" class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink" aria-label="Editar zerada de {{ $playthrough->game->title }}">
                    <x-icon name="pencil" class="size-4" />
                </a>
            </div>
        @empty
            <x-empty-state title="Nenhuma zerada registrada" description="Quando você terminar um jogo, registre aqui para acompanhar sua jornada.">
                <x-button :href="route('playthroughs.create')">Registrar zerada</x-button>
            </x-empty-state>
        @endforelse
    </x-card>

    <div class="mt-4">{{ $playthroughs->links() }}</div>
</x-app-layout>

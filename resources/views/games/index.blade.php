<x-app-layout title="Jogos" subtitle="Seu backlog e tudo o que você já jogou">
    <x-slot name="actions">
        <x-button :href="route('games.create')">
            <x-icon name="plus" class="size-4" />
            Novo jogo
        </x-button>
    </x-slot>

    <nav class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Filtrar por status">
        <a href="{{ route('games.index') }}" @class(['shrink-0 rounded-full px-3.5 py-1.5 text-sm transition', 'bg-accent text-accent-ink' => ! $status, 'bg-surface-2 text-muted hover:text-ink' => $status])>Todos</a>
        @foreach (\App\Enums\GameStatus::cases() as $option)
            <a href="{{ route('games.index', ['status' => $option->value]) }}" @class(['shrink-0 rounded-full px-3.5 py-1.5 text-sm transition', 'bg-accent text-accent-ink' => $status === $option, 'bg-surface-2 text-muted hover:text-ink' => $status !== $option])>{{ $option->label() }}</a>
        @endforeach
    </nav>

    <x-card class="divide-y divide-line">
        @forelse ($games as $game)
            <a href="{{ route('games.show', $game) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-surface-2/50">
                <x-game-cover :title="$game->title" size="sm" />
                <div class="min-w-0 flex-1">
                    <div class="truncate font-medium">{{ $game->title }}</div>
                    @if ($game->playthroughs_count > 0)
                        <div class="text-xs text-muted">{{ $game->playthroughs_count }} {{ $game->playthroughs_count === 1 ? 'zerada' : 'zeradas' }}</div>
                    @endif
                </div>
                <x-status-badge :status="$game->status" />
            </a>
        @empty
            <x-empty-state title="Nenhum jogo encontrado" description="Cadastre os jogos que você quer zerar ou registre uma zerada.">
                <x-button :href="route('games.create')">Novo jogo</x-button>
            </x-empty-state>
        @endforelse
    </x-card>

    <div class="mt-4">{{ $games->links() }}</div>
</x-app-layout>

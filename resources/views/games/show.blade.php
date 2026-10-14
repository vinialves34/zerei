<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $game->title }}</h2>
            <span class="text-sm text-gray-500">{{ $game->status->label() }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap gap-3 px-4 sm:px-0">
                <a href="{{ route('playthroughs.create', ['game' => $game]) }}"><x-primary-button type="button">Registrar zerada</x-primary-button></a>
                <a href="{{ route('games.edit', $game) }}"><x-secondary-button type="button">Editar</x-secondary-button></a>
                <form method="POST" action="{{ route('games.destroy', $game) }}" onsubmit="return confirm('Remover este jogo e todas as suas zeradas?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Remover</x-danger-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <h3 class="p-4 font-semibold text-gray-800 border-b border-gray-100">Zeradas</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($game->playthroughs as $playthrough)
                        <li class="p-4 flex flex-wrap items-center justify-between gap-2">
                            <span>
                                {{ $playthrough->completed_at->format('d/m/Y') }}
                                · {{ $playthrough->platform->label() }}
                                · {{ $playthrough->completion_level->label() }}
                            </span>
                            <span class="flex items-center gap-4">
                                <x-rating :value="$playthrough->rating" />
                                <a href="{{ route('playthroughs.edit', $playthrough) }}" class="text-sm text-indigo-600 hover:underline">Editar</a>
                            </span>
                        </li>
                    @empty
                        <li class="p-4 text-sm text-gray-500">Este jogo ainda não foi zerado.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

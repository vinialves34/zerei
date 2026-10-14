<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jogos</h2>
            <a href="{{ route('games.create') }}"><x-primary-button type="button">Novo jogo</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <nav class="flex flex-wrap gap-2 px-4 sm:px-0 text-sm">
                <a href="{{ route('games.index') }}" @class(['px-3 py-1 rounded-full', 'bg-gray-800 text-white' => ! $status, 'bg-white text-gray-700' => $status])>Todos</a>
                @foreach (\App\Enums\GameStatus::cases() as $option)
                    <a href="{{ route('games.index', ['status' => $option->value]) }}" @class(['px-3 py-1 rounded-full', 'bg-gray-800 text-white' => $status === $option, 'bg-white text-gray-700' => $status !== $option])>{{ $option->label() }}</a>
                @endforeach
            </nav>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-100">
                    @forelse ($games as $game)
                        <li class="p-4 flex items-center justify-between gap-4">
                            <a href="{{ route('games.show', $game) }}" class="text-indigo-600 hover:underline">{{ $game->title }}</a>
                            <span class="text-sm text-gray-500">
                                {{ $game->status->label() }}
                                @if ($game->playthroughs_count > 1)
                                    · {{ $game->playthroughs_count }}x
                                @endif
                            </span>
                        </li>
                    @empty
                        <li class="p-4 text-sm text-gray-500">Nenhum jogo encontrado.</li>
                    @endforelse
                </ul>
            </div>

            {{ $games->links() }}
        </div>
    </div>
</x-app-layout>

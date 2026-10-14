<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <a href="{{ route('years.show', $year) }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <div class="text-sm text-gray-500">Zeradas em {{ $year }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $completedThisYear }}</div>
                </a>
                <a href="{{ route('games.index', ['status' => 'backlog']) }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <div class="text-sm text-gray-500">Jogos para zerar</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $backlogCount }}</div>
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Jogando agora</h3>
                <ul class="mt-3 divide-y divide-gray-100">
                    @forelse ($playingGames as $game)
                        <li class="py-2">
                            <a href="{{ route('games.show', $game) }}" class="text-indigo-600 hover:underline">{{ $game->title }}</a>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Nenhum jogo em andamento.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Últimas zeradas</h3>
                    <a href="{{ route('playthroughs.create') }}" class="text-sm text-indigo-600 hover:underline">Registrar zerada</a>
                </div>
                <ul class="mt-3 divide-y divide-gray-100">
                    @forelse ($recentPlaythroughs as $playthrough)
                        <li class="py-2 flex items-center justify-between gap-4">
                            <span>{{ $playthrough->game->title }}</span>
                            <span class="text-sm text-gray-500">{{ $playthrough->completed_at->format('d/m/Y') }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Você ainda não registrou nenhuma zerada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

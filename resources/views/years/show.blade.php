<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Linha do tempo de {{ $year }}</h2>
            <span class="text-sm text-gray-500">{{ $total }} {{ $total === 1 ? 'zerada' : 'zeradas' }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <a href="{{ route('years.index') }}" class="px-4 sm:px-0 text-sm text-indigo-600 hover:underline">&larr; Todos os anos</a>

            @foreach ($timeline as $month => $playthroughs)
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <h3 class="p-4 flex items-center justify-between font-semibold text-gray-800 border-b border-gray-100">
                        <span class="capitalize">{{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F') }}</span>
                        <span class="text-sm font-normal text-gray-500">{{ $playthroughs->count() }}</span>
                    </h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($playthroughs as $playthrough)
                            <li class="p-4 flex flex-wrap items-center justify-between gap-2">
                                <span>
                                    <a href="{{ route('games.show', $playthrough->game) }}" class="text-indigo-600 hover:underline">{{ $playthrough->game->title }}</a>
                                    <span class="text-sm text-gray-500">· {{ $playthrough->platform->label() }} · {{ $playthrough->completion_level->label() }}</span>
                                </span>
                                <span class="flex items-center gap-4 text-sm text-gray-500">
                                    {{ $playthrough->completed_at->format('d/m') }}
                                    <x-rating :value="$playthrough->rating" />
                                </span>
                            </li>
                        @empty
                            <li class="p-4 text-sm text-gray-400">Nenhuma zerada neste mês.</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>

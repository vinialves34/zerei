<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Zeradas{{ $year ? " de {$year}" : '' }}</h2>
            <a href="{{ route('playthroughs.create') }}"><x-primary-button type="button">Registrar zerada</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <nav class="flex flex-wrap gap-2 px-4 sm:px-0 text-sm">
                <a href="{{ route('playthroughs.index') }}" @class(['px-3 py-1 rounded-full', 'bg-gray-800 text-white' => ! $year, 'bg-white text-gray-700' => $year])>Todos os anos</a>
                @foreach ($years as $option)
                    <a href="{{ route('playthroughs.index', ['year' => $option]) }}" @class(['px-3 py-1 rounded-full', 'bg-gray-800 text-white' => $year === $option, 'bg-white text-gray-700' => $year !== $option])>{{ $option }}</a>
                @endforeach
            </nav>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Data</th>
                            <th class="px-4 py-3 font-medium">Jogo</th>
                            <th class="px-4 py-3 font-medium">Plataforma</th>
                            <th class="px-4 py-3 font-medium">Conclusão</th>
                            <th class="px-4 py-3 font-medium">Nota</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($playthroughs as $playthrough)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $playthrough->completed_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3"><a href="{{ route('games.show', $playthrough->game) }}" class="text-indigo-600 hover:underline">{{ $playthrough->game->title }}</a></td>
                                <td class="px-4 py-3">{{ $playthrough->platform->label() }}</td>
                                <td class="px-4 py-3">{{ $playthrough->completion_level->label() }}</td>
                                <td class="px-4 py-3"><x-rating :value="$playthrough->rating" /></td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('playthroughs.edit', $playthrough) }}" class="text-indigo-600 hover:underline">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Nenhuma zerada registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $playthroughs->links() }}
        </div>
    </div>
</x-app-layout>

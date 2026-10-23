<x-app-layout title="Registros por anos" subtitle="Quantas zeradas em cada ano">
    @if ($countsByYear->isEmpty())
        <x-card>
            <x-empty-state title="Nenhum ano ainda" description="Registre sua primeira zerada para começar a linha do tempo.">
                <x-button :href="route('playthroughs.create')">Registrar zerada</x-button>
            </x-empty-state>
        </x-card>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
            @foreach ($countsByYear as $year => $count)
                <a href="{{ route('years.show', $year) }}" class="rounded-xl border border-line bg-surface p-5 transition hover:border-muted">
                    <div class="text-3xl font-semibold tracking-tight">{{ $year }}</div>
                    <div class="mt-1 text-sm text-muted">{{ $count }} {{ $count === 1 ? 'zerada' : 'zeradas' }}</div>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>

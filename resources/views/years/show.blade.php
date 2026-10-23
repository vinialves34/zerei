<x-app-layout :title="'Linha do tempo de '.$year" :subtitle="$total.' '.($total === 1 ? 'zerada' : 'zeradas')">
    <a href="{{ route('years.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-ink">
        <x-icon name="arrow-left" class="size-4" />
        Todos os anos
    </a>

    <x-card class="mb-8 p-4 sm:p-5">
        <x-month-chart :counts="$timeline->map->count()" />
    </x-card>

    <ol class="ms-2 space-y-6 border-s border-line">
        @foreach ($timeline as $month => $playthroughs)
            <li class="relative ps-6">
                <span @class(['absolute -start-[5px] top-1.5 size-2.5 rounded-full', 'bg-accent' => $playthroughs->isNotEmpty(), 'bg-line' => $playthroughs->isEmpty()]) aria-hidden="true"></span>

                <h2 class="flex items-baseline gap-2">
                    <span class="font-semibold">{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create($year, $month, 1)->translatedFormat('F')) }}</span>
                    <span class="text-sm text-muted">{{ $playthroughs->count() }}</span>
                </h2>

                @if ($playthroughs->isNotEmpty())
                    <x-card class="mt-3 divide-y divide-line">
                        @foreach ($playthroughs as $playthrough)
                            <a href="{{ route('games.show', $playthrough->game) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-surface-2/50">
                                <x-game-cover :title="$playthrough->game->title" :url="$playthrough->game->cover_url" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <div class="truncate font-medium">{{ $playthrough->game->title }}</div>
                                    <div class="truncate text-xs text-muted">
                                        {{ $playthrough->completed_at->format('d/m') }} · {{ $playthrough->platform->label() }} · {{ $playthrough->completion_level->label() }}
                                    </div>
                                </div>
                                <x-rating :value="$playthrough->rating" class="text-sm" />
                            </a>
                        @endforeach
                    </x-card>
                @endif
            </li>
        @endforeach
    </ol>
</x-app-layout>

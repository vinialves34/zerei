@props(['level', 'year', 'yearPoints'])

<div {{ $attributes }}>
    <div class="flex items-center gap-4">
        <span class="flex size-14 shrink-0 flex-col items-center justify-center rounded-full bg-accent/15 text-accent" aria-hidden="true">
            <span class="text-[10px] font-medium uppercase leading-none tracking-wide">Nível</span>
            <span class="text-xl font-semibold leading-tight">{{ $level['level'] }}</span>
        </span>
        <div class="min-w-0">
            <p class="text-lg font-semibold tracking-tight">{{ $level['title']->label() }}</p>
            <p class="text-sm text-muted">Nível {{ $level['level'] }} · {{ number_format($level['points'], 0, ',', '.') }} pontos</p>
        </div>
    </div>

    <div class="mt-4 flex items-baseline justify-between gap-3 text-sm">
        <span class="text-muted">{{ number_format($level['pointsIntoLevel'], 0, ',', '.') }} de {{ number_format($level['pointsForNextLevel'], 0, ',', '.') }} pontos</span>
        <span class="text-muted">Faltam {{ number_format($level['remaining'], 0, ',', '.') }} para o nível {{ $level['level'] + 1 }}</span>
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-label="Progresso até o nível {{ $level['level'] + 1 }}" aria-valuemin="0" aria-valuemax="{{ $level['pointsForNextLevel'] }}" aria-valuenow="{{ $level['pointsIntoLevel'] }}">
        <div class="h-full rounded-full bg-accent transition-all" style="width: {{ $level['percent'] }}%"></div>
    </div>

    <p class="mt-4 text-sm text-muted">Pontos de {{ $year }}: <span class="font-medium text-ink">{{ number_format($yearPoints, 0, ',', '.') }}</span></p>
</div>

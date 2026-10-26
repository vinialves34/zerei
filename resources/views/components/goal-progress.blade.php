@props(['progress', 'year'])

<div {{ $attributes }}>
    <div class="flex items-baseline justify-between gap-3">
        <span class="text-2xl font-semibold tracking-tight">{{ $progress['completed'] }}<span class="text-base font-normal text-muted"> de {{ $progress['target'] }} zeradas</span></span>
        @if ($progress['reached'])
            <span class="inline-flex items-center gap-1 text-sm text-ok"><x-icon name="trophy" class="size-4" />Meta batida!</span>
        @else
            <span class="text-sm text-muted">{{ $progress['remaining'] === 1 ? 'Falta 1' : 'Faltam '.$progress['remaining'] }}</span>
        @endif
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-label="Progresso da meta de {{ $year }}" aria-valuemin="0" aria-valuemax="{{ $progress['target'] }}" aria-valuenow="{{ min($progress['completed'], $progress['target']) }}">
        <div @class(['h-full rounded-full transition-all', 'bg-ok' => $progress['reached'], 'bg-accent' => ! $progress['reached']]) style="width: {{ $progress['percent'] }}%"></div>
    </div>
</div>

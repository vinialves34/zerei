@props(['achievements'])

<div {{ $attributes }}>
    <h2 class="text-lg font-semibold tracking-tight">Conquistas</h2>
    <p class="mt-1 text-sm text-muted">{{ $achievements->where('unlocked', true)->count() }} de {{ $achievements->count() }} desbloqueadas</p>

    <ul class="mt-4 space-y-3">
        @foreach ($achievements as $item)
            <li @class(['flex items-center gap-3', 'opacity-50' => ! $item['unlocked']]) @if ($item['unlocked']) data-unlocked @endif>
                <span @class(['flex size-10 shrink-0 items-center justify-center rounded-full', 'bg-gold/15 text-gold' => $item['unlocked'], 'bg-surface-2 text-muted' => ! $item['unlocked']])>
                    <x-icon :name="$item['achievement']->icon()" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-medium">{{ $item['achievement']->label() }}</p>
                    <p class="text-xs text-muted">{{ $item['achievement']->description() }}</p>
                </div>
                <span class="sr-only">{{ $item['unlocked'] ? 'Desbloqueada' : 'Bloqueada' }}</span>
            </li>
        @endforeach
    </ul>
</div>

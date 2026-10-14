@props(['counts', 'highlight' => null])

@php
$max = max(1, $counts->max());
@endphp

<div {{ $attributes->merge(['class' => 'flex h-36 items-end gap-1.5 sm:gap-2']) }} role="img" aria-label="Zeradas por mês">
    @foreach ($counts as $month => $count)
        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1.5" title="{{ $count }} {{ $count === 1 ? 'zerada' : 'zeradas' }}">
            <span class="text-[11px] leading-none {{ $count ? 'text-muted' : 'text-transparent' }}">{{ $count }}</span>
            <span @class(['w-full rounded-t', 'bg-accent' => $month === $highlight, 'bg-surface-2' => $month !== $highlight]) style="height: {{ max(4, round($count / $max * 100)) }}%"></span>
            <span class="text-[11px] leading-none text-muted">{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create(null, $month, 1)->translatedFormat('M')) }}</span>
        </div>
    @endforeach
</div>

@props(['active' => false, 'icon'])

@php
$classes = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent '
    .($active ? 'bg-surface-2 text-ink' : 'text-muted hover:bg-surface-2/60 hover:text-ink');
@endphp

<a {{ $attributes->merge(['class' => $classes, 'aria-current' => $active ? 'page' : null]) }}>
    <x-icon :name="$icon" :class="$active ? 'text-accent' : ''" />
    {{ $slot }}
</a>

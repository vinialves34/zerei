@props(['status'])

@php
$classes = match ($status) {
    \App\Enums\GameStatus::Playing => 'bg-ok/15 text-ok',
    \App\Enums\GameStatus::Completed => 'bg-accent/15 text-accent',
    \App\Enums\GameStatus::Dropped => 'bg-danger/15 text-danger',
    default => 'bg-surface-2 text-muted',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $status->label() }}
</span>

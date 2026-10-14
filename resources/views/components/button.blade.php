@props(['variant' => 'primary', 'href' => null])

@php
$classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-app disabled:opacity-50 '
    .match ($variant) {
        'secondary' => 'border border-line bg-surface-2 text-ink hover:border-muted',
        'danger' => 'bg-danger text-app hover:brightness-110',
        'ghost' => 'text-muted hover:bg-surface-2 hover:text-ink',
        default => 'bg-accent text-accent-ink hover:brightness-110',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif

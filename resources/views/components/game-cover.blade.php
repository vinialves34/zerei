@props(['title', 'size' => 'md'])

@php
$palette = ['bg-blue-700', 'bg-rose-700', 'bg-emerald-700', 'bg-violet-700', 'bg-amber-700', 'bg-cyan-700', 'bg-fuchsia-700', 'bg-teal-700'];

$color = $palette[crc32($title) % count($palette)];

$initials = \Illuminate\Support\Str::of($title)
    ->explode(' ')
    ->filter()
    ->take(2)
    ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
    ->implode('');

$sizeClasses = match ($size) {
    'sm' => 'size-11 rounded-lg text-sm',
    'lg' => 'w-24 aspect-[3/4] rounded-xl text-3xl sm:w-32',
    default => 'w-full aspect-[3/4] rounded-xl text-2xl',
};
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-end p-2.5 font-semibold text-white/95 {$color} {$sizeClasses}"]) }} aria-hidden="true">{{ $initials }}</div>

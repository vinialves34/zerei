@props(['overlay' => false])

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center text-gold',
    'rounded-full bg-app/80 p-1.5 shadow ring-1 ring-gold/40 backdrop-blur' => $overlay,
])->merge(['title' => 'Platina', 'role' => 'img', 'aria-label' => 'Platina', 'data-platinum' => '']) }}>
    <x-icon name="trophy" class="size-4 fill-gold/25" />
</span>

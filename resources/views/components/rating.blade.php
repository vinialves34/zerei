@props(['value'])

<span {{ $attributes->merge(['class' => 'inline-flex whitespace-nowrap tracking-wide']) }} title="{{ $value }} de 5" role="img" aria-label="{{ $value }} de 5 estrelas">
    <span class="text-gold" aria-hidden="true">{{ str_repeat('★', $value) }}</span><span class="text-line" aria-hidden="true">{{ str_repeat('★', 5 - $value) }}</span>
</span>

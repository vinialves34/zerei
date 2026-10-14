@props(['value'])

<span {{ $attributes->merge(['class' => 'text-amber-500 whitespace-nowrap']) }} title="{{ $value }} de 5" aria-label="{{ $value }} de 5 estrelas">
    {{ str_repeat('★', $value) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $value) }}</span>
</span>

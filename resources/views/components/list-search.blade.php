@props(['action', 'sort', 'defaultSort', 'options', 'search' => null, 'hidden' => []])

@php
$hidden = array_filter($hidden, fn ($value) => filled($value));
$clearUrl = $action.($hidden ? '?'.http_build_query($hidden) : '');
@endphp

<form method="GET" action="{{ $action }}" role="search" class="mb-4 flex flex-col gap-2 sm:flex-row">
    @foreach ($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <div class="relative flex-1">
        <label for="list-search" class="sr-only">Buscar por título</label>
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
        <x-text-input id="list-search" type="search" name="search" :value="$search" placeholder="Buscar por título" maxlength="100" autocomplete="off" class="block w-full pl-9" />
    </div>

    <label for="list-sort" class="sr-only">Ordenar por</label>
    <x-select-input id="list-sort" name="sort" class="sm:w-48" x-data x-on:change="$el.form.submit()">
        @foreach ($options as $option)
            <option value="{{ $option->value }}" @selected($option === $sort)>{{ $option->label() }}</option>
        @endforeach
    </x-select-input>

    <div class="flex items-center gap-2">
        <x-button variant="secondary">Buscar</x-button>
        @if (filled($search) || $sort !== $defaultSort)
            <x-button :href="$clearUrl" variant="ghost">Limpar</x-button>
        @endif
    </div>
</form>

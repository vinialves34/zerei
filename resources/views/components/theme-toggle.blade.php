<button
    type="button"
    x-data="{ light: document.documentElement.dataset.theme === 'light' }"
    x-on:click="light = ! light; document.documentElement.dataset.theme = light ? 'light' : 'dark'; try { localStorage.setItem('theme', light ? 'light' : 'dark') } catch (e) {}"
    x-bind:aria-label="light ? 'Usar tema escuro' : 'Usar tema claro'"
    aria-label="Alternar tema"
    {{ $attributes->merge(['class' => 'inline-flex size-9 items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent']) }}
>
    <x-icon name="sun" x-show="! light" />
    <x-icon name="moon" x-show="light" style="display: none" />
</button>

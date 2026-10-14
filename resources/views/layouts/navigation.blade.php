@php
$links = [
    ['route' => 'dashboard', 'active' => 'dashboard', 'label' => 'Painel', 'icon' => 'dashboard'],
    ['route' => 'playthroughs.index', 'active' => 'playthroughs.*', 'label' => 'Zeradas', 'icon' => 'trophy'],
    ['route' => 'games.index', 'active' => 'games.*', 'label' => 'Jogos', 'icon' => 'library'],
    ['route' => 'years.index', 'active' => 'years.*', 'label' => 'Anos', 'icon' => 'calendar'],
];

$initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1));
@endphp

<aside class="fixed inset-y-0 left-0 z-30 hidden w-60 flex-col border-r border-line bg-surface p-4 lg:flex">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 pb-6 pt-1 text-lg font-semibold">
        <x-application-logo />
        {{ config('app.name') }}
    </a>

    <nav class="flex flex-col gap-1" aria-label="Principal">
        @foreach ($links as $link)
            <x-nav-link :href="route($link['route'])" :icon="$link['icon']" :active="request()->routeIs($link['active'])">
                {{ $link['label'] }}
            </x-nav-link>
        @endforeach
    </nav>

    <x-button :href="route('playthroughs.create')" class="mt-6">
        <x-icon name="plus" class="size-4" />
        Registrar zerada
    </x-button>

    <div class="mt-auto border-t border-line pt-4">
        <div class="flex items-center gap-1">
            <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-lg p-2 transition hover:bg-surface-2" title="{{ __('Profile') }}">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-2 text-xs font-medium">{{ $initials }}</span>
                <span class="truncate text-sm">{{ Auth::user()->name }}</span>
            </a>
            <x-theme-toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="{{ __('Log Out') }}" title="{{ __('Log Out') }}">
                    <x-icon name="logout" />
                </button>
            </form>
        </div>
    </div>
</aside>

<header class="sticky top-0 z-30 flex items-center justify-between border-b border-line bg-surface/95 px-4 py-2.5 backdrop-blur lg:hidden">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-semibold">
        <x-application-logo class="size-8" />
        {{ config('app.name') }}
    </a>

    <div class="flex items-center gap-1">
        <x-theme-toggle />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="flex size-9 items-center justify-center rounded-full bg-surface-2 text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="Menu do usuário">
                    {{ $initials }}
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="border-b border-line px-4 py-2 text-xs text-muted">{{ Auth::user()->email }}</div>

                <x-dropdown-link :href="route('profile.edit')">
                    <x-icon name="user" class="size-4" />
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm text-ink transition hover:bg-surface-2 focus:bg-surface-2 focus:outline-none">
                        <x-icon name="logout" class="size-4" />
                        {{ __('Log Out') }}
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>

@if (request()->routeIs('dashboard', 'playthroughs.index', 'games.index', 'years.*'))
    <a href="{{ route('playthroughs.create') }}" class="fixed bottom-20 right-4 z-30 flex size-14 items-center justify-center rounded-full bg-accent text-accent-ink shadow-lg shadow-black/30 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-app lg:hidden" aria-label="Registrar zerada">
        <x-icon name="plus" class="size-6" />
    </a>
@endif

<nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-line bg-surface/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" aria-label="Principal">
    @foreach ($links as $link)
        @php($active = request()->routeIs($link['active']))
        <a href="{{ route($link['route']) }}" @class(['flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition', 'text-accent' => $active, 'text-muted hover:text-ink' => ! $active]) @if ($active) aria-current="page" @endif>
            <x-icon :name="$link['icon']" />
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>

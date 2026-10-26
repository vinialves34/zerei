{{-- Expects $game (optional), $titles (optional, titles of the user's games for suggestions), $titleHelp and $initialTitle (optional). --}}
<div
    class="space-y-6"
    x-data="coverPicker({
        searchUrl: @js(route('games.rawg-search')),
        title: @js(old('title', $initialTitle ?? $game->title ?? '')),
        coverUrl: @js(old('cover_url', $game->cover_url ?? '')),
        rawgId: @js(old('rawg_id', $game->rawg_id ?? '')),
    })"
    x-on:click.outside="open = false"
    x-on:keydown.escape="open = false"
>
    <div class="relative">
        <x-input-label for="title" value="Título" />
        <div class="relative mt-1">
            <x-text-input id="title" name="title" type="text" class="block w-full pr-10" x-model="title" x-on:input="onTitleInput()" required autofocus autocomplete="off" :list="isset($titles) ? 'game-titles' : null" />
            <span x-show="loading" x-cloak class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-muted" role="status">
                <x-icon name="loader" class="size-4 animate-spin" />
                <span class="sr-only">Buscando jogos na RAWG…</span>
            </span>
        </div>
        @isset($titles)
            <datalist id="game-titles">
                @foreach ($titles as $gameTitle)
                    <option value="{{ $gameTitle }}"></option>
                @endforeach
            </datalist>
        @endisset
        @isset($titleHelp)
            <p class="mt-1.5 text-sm text-muted">{{ $titleHelp }}</p>
        @endisset
        <x-input-error :messages="$errors->get('title')" class="mt-2" />

        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-line bg-surface shadow-lg">
            <template x-for="result in results" :key="result.id">
                <button type="button" class="flex w-full items-center gap-3 px-3 py-2 text-left transition hover:bg-surface-2" x-on:click="choose(result)">
                    <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded bg-surface-2">
                        <img x-show="result.cover_url" :src="result.cover_url" alt="" loading="lazy" referrerpolicy="no-referrer" class="size-full object-cover">
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium" x-text="result.name"></span>
                        <span class="block text-xs text-muted" x-show="result.released" x-text="result.released ? result.released.slice(0, 4) : ''"></span>
                    </span>
                </button>
            </template>
            <p x-show="! results.length && ! unavailable" class="px-3 py-2 text-sm text-muted">Nenhum jogo encontrado na RAWG. Você pode colar o link de uma capa abaixo.</p>
            <p x-show="unavailable" class="px-3 py-2 text-sm text-muted">Busca da RAWG indisponível no momento. Você pode colar o link de uma capa abaixo.</p>
        </div>
    </div>

    <div>
        <x-input-label for="cover_url" value="URL da capa (opcional)" />
        <div class="mt-1 flex items-start gap-3">
            <div class="min-w-0 flex-1">
                <x-text-input id="cover_url" name="cover_url" type="url" class="block w-full" placeholder="https://" x-model="coverUrl" x-on:input="onCoverInput()" autocomplete="off" />
                <input type="hidden" name="rawg_id" x-model="rawgId">
            </div>
            <button type="button" class="shrink-0 self-center text-sm text-muted transition hover:text-ink" x-show="coverUrl" x-cloak x-on:click="clearCover()">Remover</button>
        </div>
        <p class="mt-1.5 text-sm text-muted">Busque o título acima para usar a capa da RAWG ou, se não achar, cole o link de uma imagem.</p>
        <x-input-error :messages="$errors->get('cover_url')" class="mt-2" />
        <x-input-error :messages="$errors->get('rawg_id')" class="mt-2" />

        <div class="mt-3 h-28 w-48 overflow-hidden rounded-lg bg-surface-2" x-show="coverUrl" x-cloak>
            <img :src="coverUrl" alt="" referrerpolicy="no-referrer" class="size-full object-cover">
        </div>
    </div>
</div>

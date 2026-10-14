@php
    $platform = old('platform', isset($playthrough) ? $playthrough->platform->value : null);
    $level = old('completion_level', isset($playthrough) ? $playthrough->completion_level->value : \App\Enums\CompletionLevel::Story->value);
    $rating = (int) old('rating', isset($playthrough) ? $playthrough->rating : 3);
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="completed_at" value="Data de conclusão" />
        <x-text-input id="completed_at" name="completed_at" type="date" class="mt-1 block w-full" :value="old('completed_at', isset($playthrough) ? $playthrough->completed_at->toDateString() : now()->toDateString())" max="{{ now()->toDateString() }}" required />
        <x-input-error :messages="$errors->get('completed_at')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="platform" value="Plataforma" />
        <x-select-input id="platform" name="platform" class="mt-1 block w-full" required>
            <option value="" disabled @selected(! $platform)>Selecione…</option>
            @foreach (\App\Enums\Platform::cases() as $option)
                <option value="{{ $option->value }}" @selected($platform === $option->value)>{{ $option->label() }}</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('platform')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label value="Nível de conclusão" />
    <div class="mt-2 grid grid-cols-3 gap-2">
        @foreach (\App\Enums\CompletionLevel::cases() as $option)
            <label class="cursor-pointer">
                <input type="radio" name="completion_level" value="{{ $option->value }}" class="peer sr-only" @checked($level === $option->value) required>
                <span class="flex items-center justify-center rounded-lg border border-line bg-surface-2 px-3 py-2.5 text-sm text-muted transition peer-checked:border-accent peer-checked:bg-accent/15 peer-checked:text-ink peer-focus-visible:ring-2 peer-focus-visible:ring-accent">{{ $option->label() }}</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('completion_level')" class="mt-2" />
</div>

<fieldset x-data="{ rating: {{ $rating }}, hover: 0 }">
    <legend class="block text-sm font-medium">Nota</legend>
    <div class="mt-2 flex items-center gap-1" x-on:mouseleave="hover = 0">
        @foreach (range(1, 5) as $star)
            <label class="cursor-pointer" x-on:mouseenter="hover = {{ $star }}">
                <input type="radio" name="rating" value="{{ $star }}" class="peer sr-only" x-model.number="rating" @checked($rating === $star) required>
                <span class="block rounded text-3xl leading-none transition peer-focus-visible:ring-2 peer-focus-visible:ring-accent" x-bind:class="(hover || rating) >= {{ $star }} ? 'text-gold' : 'text-line'" aria-label="{{ $star }} {{ $star === 1 ? 'estrela' : 'estrelas' }}">★</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('rating')" class="mt-2" />
</fieldset>

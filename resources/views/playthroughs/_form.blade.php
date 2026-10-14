<div>
    <x-input-label for="completed_at" value="Data de conclusão" />
    <x-text-input id="completed_at" name="completed_at" type="date" class="mt-1 block w-full" :value="old('completed_at', isset($playthrough) ? $playthrough->completed_at->toDateString() : now()->toDateString())" max="{{ now()->toDateString() }}" required />
    <x-input-error :messages="$errors->get('completed_at')" class="mt-2" />
</div>

<div>
    <x-input-label for="platform" value="Plataforma" />
    <x-select-input id="platform" name="platform" class="mt-1 block w-full" required>
        <option value="" disabled @selected(! old('platform', isset($playthrough) ? $playthrough->platform->value : null))>Selecione…</option>
        @foreach (\App\Enums\Platform::cases() as $option)
            <option value="{{ $option->value }}" @selected(old('platform', isset($playthrough) ? $playthrough->platform->value : null) === $option->value)>{{ $option->label() }}</option>
        @endforeach
    </x-select-input>
    <x-input-error :messages="$errors->get('platform')" class="mt-2" />
</div>

<div>
    <x-input-label for="completion_level" value="Nível de conclusão" />
    <x-select-input id="completion_level" name="completion_level" class="mt-1 block w-full" required>
        @foreach (\App\Enums\CompletionLevel::cases() as $option)
            <option value="{{ $option->value }}" @selected(old('completion_level', isset($playthrough) ? $playthrough->completion_level->value : \App\Enums\CompletionLevel::Story->value) === $option->value)>{{ $option->label() }}</option>
        @endforeach
    </x-select-input>
    <x-input-error :messages="$errors->get('completion_level')" class="mt-2" />
</div>

<div>
    <x-input-label for="rating" value="Nota" />
    <x-select-input id="rating" name="rating" class="mt-1 block w-full" required>
        @foreach (range(5, 1) as $stars)
            <option value="{{ $stars }}" @selected((int) old('rating', isset($playthrough) ? $playthrough->rating : 3) === $stars)>{{ str_repeat('★', $stars) }} ({{ $stars }})</option>
        @endforeach
    </x-select-input>
    <x-input-error :messages="$errors->get('rating')" class="mt-2" />
</div>

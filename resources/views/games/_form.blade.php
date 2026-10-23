@php
    $isCompleted = isset($game) && $game->status === \App\Enums\GameStatus::Completed;
    $selectedStatus = old('status', isset($game) && ! $isCompleted ? $game->status->value : \App\Enums\GameStatus::Backlog->value);
@endphp

@include('games._cover-fields')

<div>
    <x-input-label for="status" value="Status" />
    <x-select-input id="status" name="status" class="mt-1 block w-full">
        @if ($isCompleted)
            <option value="">Zerado (manter)</option>
        @endif
        @foreach ([\App\Enums\GameStatus::Backlog, \App\Enums\GameStatus::Playing, \App\Enums\GameStatus::Dropped] as $option)
            <option value="{{ $option->value }}" @selected($selectedStatus === $option->value)>{{ $option->label() }}</option>
        @endforeach
    </x-select-input>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

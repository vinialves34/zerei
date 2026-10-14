<x-app-layout title="Editar jogo" :subtitle="$game->title">
    <form method="POST" action="{{ route('games.update', $game) }}" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')

        <x-card class="space-y-6 p-5 sm:p-6">
            @include('games._form')
        </x-card>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-button :href="route('games.show', $game)" variant="ghost">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-app-layout>

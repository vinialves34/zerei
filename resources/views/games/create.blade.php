<x-app-layout title="Novo jogo">
    <form method="POST" action="{{ route('games.store') }}" class="max-w-xl space-y-6">
        @csrf

        <x-card class="space-y-6 p-5 sm:p-6">
            @include('games._form')
        </x-card>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-button :href="route('games.index')" variant="ghost">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-app-layout>

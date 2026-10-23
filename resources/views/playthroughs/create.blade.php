<x-app-layout title="Registrar zerada" subtitle="Cadastre um jogo que você terminou">
    <form method="POST" action="{{ route('playthroughs.store') }}" class="max-w-xl space-y-6">
        @csrf

        <x-card class="space-y-6 p-5 sm:p-6">
            @include('games._cover-fields', ['game' => null, 'initialTitle' => $title, 'titles' => $titles, 'titleHelp' => 'Escolha um jogo já cadastrado, busque na RAWG ou digite um novo título.'])

            @include('playthroughs._form')
        </x-card>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-button :href="route('playthroughs.index')" variant="ghost">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-app-layout>

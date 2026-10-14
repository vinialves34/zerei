<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar zerada</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('playthroughs.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf

                <div>
                    <x-input-label for="title" value="Jogo" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" list="game-titles" :value="old('title', $title)" required autofocus />
                    <datalist id="game-titles">
                        @foreach ($titles as $gameTitle)
                            <option value="{{ $gameTitle }}"></option>
                        @endforeach
                    </datalist>
                    <p class="mt-1 text-sm text-gray-500">Escolha um jogo já cadastrado ou digite um novo título.</p>
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                @include('playthroughs._form')

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('playthroughs.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

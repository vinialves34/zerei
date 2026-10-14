<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar zerada: {{ $playthrough->game->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('playthroughs.update', $playthrough) }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')
                @include('playthroughs._form')

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('playthroughs.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                </div>
            </form>

            <form method="POST" action="{{ route('playthroughs.destroy', $playthrough) }}" onsubmit="return confirm('Remover esta zerada?')" class="px-4 sm:px-0">
                @csrf
                @method('DELETE')
                <x-danger-button>Remover zerada</x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>

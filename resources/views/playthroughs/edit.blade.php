<x-app-layout title="Editar zerada" :subtitle="$playthrough->game->title">
    <form method="POST" action="{{ route('playthroughs.update', $playthrough) }}" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')

        <x-card class="space-y-6 p-5 sm:p-6">
            @include('playthroughs._form')
        </x-card>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-button :href="route('playthroughs.index')" variant="ghost">{{ __('Cancel') }}</x-button>
        </div>
    </form>

    <form method="POST" action="{{ route('playthroughs.destroy', $playthrough) }}" onsubmit="return confirm('Remover esta zerada?')" class="mt-10 max-w-xl border-t border-line pt-6">
        @csrf
        @method('DELETE')
        <x-button variant="ghost" class="text-danger hover:text-danger">
            <x-icon name="trash" class="size-4" />
            Remover zerada
        </x-button>
    </form>
</x-app-layout>

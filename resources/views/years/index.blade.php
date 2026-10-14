<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Zeradas por ano</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-100">
                    @forelse ($countsByYear as $year => $count)
                        <li>
                            <a href="{{ route('years.show', $year) }}" class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <span class="text-lg font-semibold text-gray-800">{{ $year }}</span>
                                <span class="text-gray-600">{{ $count }} {{ $count === 1 ? 'zerada' : 'zeradas' }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="p-4 text-sm text-gray-500">Você ainda não registrou nenhuma zerada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

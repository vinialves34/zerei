<x-app-layout :title="__('Profile')">
    <div class="max-w-xl space-y-6">
        <x-card class="p-5 sm:p-6">
            @include('profile.partials.update-profile-information-form')
        </x-card>

        <x-card class="p-5 sm:p-6">
            @include('profile.partials.update-password-form')
        </x-card>

        <x-card class="p-5 sm:p-6">
            @include('profile.partials.delete-user-form')
        </x-card>
    </div>
</x-app-layout>

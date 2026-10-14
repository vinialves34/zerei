<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        @include('layouts.head')
    </head>
    <body class="min-h-screen bg-app font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-xl font-semibold">
                <x-application-logo class="size-11" />
                {{ config('app.name') }}
            </a>

            <x-card class="mt-8 w-full max-w-md p-6 sm:p-8">
                {{ $slot }}
            </x-card>

            <x-theme-toggle class="mt-6" />
        </div>
    </body>
</html>

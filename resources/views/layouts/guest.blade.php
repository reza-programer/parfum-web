<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:600|inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="flex min-h-screen flex-col items-center px-4 pb-16 pt-14 sm:justify-center sm:pt-0">
            <a href="/" wire:navigate aria-label="{{ config('app.name') }}">
                <x-application-logo />
            </a>

            <div class="mt-8 w-full rounded-xl border border-line bg-paper-card px-5 py-8 shadow-card sm:max-w-[420px] sm:px-8">
                {{ $slot }}
            </div>
        </div>
        <x-ui.toast />
        <livewire:chat-widget />
    </body>
</html>

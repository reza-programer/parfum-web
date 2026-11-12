<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:600|inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen">
        <div class="flex min-h-screen flex-col">
            {{-- Bilah info: hanya fakta layanan toko, tanpa promo. Tidak ikut menempel. --}}
            <div class="hidden bg-ink text-white/80 md:block">
                <div class="page flex h-8 items-center justify-between gap-6 text-caption">
                    <p class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5"><x-lucide-shield-check class="size-3.5" stroke-width="2" /> Parfum original dan decant</span>
                        <span class="inline-flex items-center gap-1.5"><x-lucide-truck class="size-3.5" stroke-width="2" /> Kirim ke seluruh Indonesia</span>
                    </p>
                    <p class="inline-flex items-center gap-1.5"><x-lucide-wallet class="size-3.5" stroke-width="2" /> Bayar lewat VA bank dan QRIS</p>
                </div>
            </div>

            <livewire:layout.navigation />

            @if (isset($header))
                <header class="page pt-10 pb-2">
                    {{ $header }}
                </header>
            @endif

            <main class="flex-1">
                {{ $slot }}
            </main>

            <x-shop.footer />
        </div>

        @stack('modals')
        <x-ui.toast />
        <livewire:chat-widget />
    </body>
</html>

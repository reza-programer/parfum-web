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
            {{-- Bilah utilitas abu-abu tipis. Tidak ikut menempel. --}}
            <div class="hidden bg-paper-raised md:block">
                <div class="page flex h-8 items-center justify-between gap-6 text-caption text-ink">
                    <p>Parfum original dan decant</p>
                    <div class="flex items-center gap-5">
                        <a href="{{ route('orders.index') }}" wire:navigate class="inline-flex items-center gap-1.5 hover:underline hover:underline-offset-4">
                            <x-lucide-package class="size-3.5" stroke-width="1.75" /> Lacak pesanan
                        </a>
                        <button type="button" x-data x-on:click="$dispatch('open-chat')" class="inline-flex items-center gap-1.5 hover:underline hover:underline-offset-4">
                            <x-lucide-message-circle class="size-3.5" stroke-width="1.75" /> Bantuan
                        </button>
                    </div>
                </div>
            </div>

            <livewire:layout.navigation />

            {{-- Bilah pengumuman: hanya fakta layanan, tanpa promo yang tidak kita punya. --}}
            <div class="border-b border-line bg-paper-card">
                <p class="page flex h-9 items-center justify-center text-center text-caption font-bold uppercase tracking-nav text-ink">
                    Original <span class="mx-2 text-ink-faint" aria-hidden="true">·</span> Bayar VA bank &amp; QRIS <span class="mx-2 hidden text-ink-faint sm:inline" aria-hidden="true">·</span> <span class="hidden sm:inline">Kirim ke seluruh Indonesia</span>
                </p>
            </div>

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

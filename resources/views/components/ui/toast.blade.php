{{--
    Toast global. Dipicu dari Livewire: $this->dispatch('toast', message: 'Ditambahkan ke keranjang').
    Juga membaca session('toast') supaya pesan tetap muncul setelah redirect.
--}}
<div x-data="{
        items: [],
        next: 0,
        show(message) {
            if (! message) return;
            const id = ++this.next;
            this.items.push({ id, message });
            setTimeout(() => this.items = this.items.filter(i => i.id !== id), 3200);
        },
     }"
     x-init="@if (session('toast')) show(@js(session('toast'))) @endif"
     x-on:toast.window="show($event.detail.message ?? $event.detail[0]?.message)"
     class="pointer-events-none fixed inset-x-0 bottom-24 z-50 flex flex-col items-center gap-2 px-4 sm:bottom-8"
     role="status" aria-live="polite">
    <template x-for="item in items" :key="item.id">
        <div x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0"
             class="pointer-events-auto min-w-[16rem] max-w-sm bg-ink/90 px-5 py-3 text-center text-body font-medium text-white shadow-float"
             x-text="item.message"></div>
    </template>
</div>

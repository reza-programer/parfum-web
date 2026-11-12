<div class="chat-launcher fixed bottom-4 right-4 z-40 transition-[bottom] duration-200 sm:bottom-6 sm:right-6">
    @if ($open)
        <section wire:poll.4s="poll" class="mb-3 flex h-[min(560px,calc(100vh-7rem))] w-[calc(100vw-2rem)] max-w-sm flex-col overflow-hidden border border-line bg-paper-card" aria-label="Chat">
            <header class="flex items-center justify-between border-b border-line px-5 py-3.5">
                <div>
                    <p class="eyebrow text-ink">Tanya kami</p>
                    <p class="meta mt-0.5 flex items-center gap-1.5">
                        @if ($withAdmin)
                            <span class="size-1.5 rounded-full bg-ink"></span> Terhubung ke admin
                        @else
                            Dijawab otomatis, admin siap bantu
                        @endif
                    </p>
                </div>
                <button type="button" wire:click="$set('open', false)" class="-mr-2 inline-flex size-11 items-center justify-center text-ink-2 hover:text-ink" aria-label="Tutup chat">
                    <x-lucide-x class="size-5" stroke-width="1.25" />
                </button>
            </header>

            <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4" x-data x-init="$el.scrollTop = $el.scrollHeight" x-effect="$nextTick(() => $el.scrollTop = $el.scrollHeight)" aria-live="polite">
                <div class="max-w-[85%] bg-paper px-3.5 py-2.5 text-body leading-6 text-ink">
                    Halo, ada yang bisa dibantu? Pilih pertanyaan di bawah atau tulis sendiri.
                </div>

                @foreach ($this->conversation?->messages ?? [] as $message)
                    @php($mine = $message->sender_type === \App\Enums\MessageSender::Customer)
                    <div wire:key="m-{{ $message->id }}" @class(['flex', 'justify-end' => $mine])>
                        <div @class([
                            'max-w-[85%] whitespace-pre-line px-3.5 py-2.5 text-body leading-6',
                            'bg-ink text-white' => $mine,
                            'bg-paper text-ink' => ! $mine,
                        ])>
                            @if ($message->sender_type === \App\Enums\MessageSender::Admin)
                                <span class="mb-0.5 block text-caption font-medium uppercase tracking-label text-ink-2">Admin</span>
                            @endif
                            {{ $message->body }}
                            @if (! auth()->check() && $message->body === \App\Services\ChatService::LOGIN_MESSAGE)
                                <a href="{{ route('login') }}" class="mt-1 block font-medium underline underline-offset-4">Masuk</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @unless ($withAdmin)
                <div class="flex flex-wrap gap-2 border-t border-line px-5 pt-3">
                    @foreach ($quickQuestions as $faq)
                        <button type="button" wire:click="ask(@js($faq->question))" class="inline-flex h-9 items-center border border-line px-3 text-label text-ink-2 hover:border-ink hover:text-ink">{{ $faq->question }}</button>
                    @endforeach
                    <button type="button" wire:click="talkToAdmin" class="inline-flex h-9 items-center border border-ink px-3 text-label text-ink hover:bg-ink hover:text-white">Bicara dengan admin</button>
                </div>
            @endunless

            <form wire:submit="send" class="flex items-end gap-2 px-5 py-3">
                <label for="chat-body" class="sr-only">Pesan</label>
                <textarea id="chat-body" wire:model="body" rows="1" maxlength="1000" placeholder="Tulis pesan"
                          x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.send() }"
                          class="field max-h-28 min-h-11 resize-none py-2.5"></textarea>
                <button type="submit" class="inline-flex size-11 shrink-0 items-center justify-center bg-ink text-white hover:bg-ink/85" aria-label="Kirim">
                    <x-lucide-send-horizontal class="size-4" stroke-width="1.25" />
                </button>
            </form>
            @error('body') <p class="px-5 pb-3 text-label text-danger">{{ $message }}</p> @enderror
        </section>
    @endif

    <div class="flex justify-end">
        {{-- Mobile: ikon persegi supaya tidak menutupi konten; layar lebar: ikon + teks --}}
        <button type="button" wire:click="$toggle('open')"
                class="inline-flex size-12 items-center justify-center gap-2 bg-ink text-white transition-colors duration-150 hover:bg-ink/85 sm:w-auto sm:px-5"
                aria-label="{{ $open ? 'Tutup chat' : 'Buka chat' }}" aria-expanded="{{ $open ? 'true' : 'false' }}">
            @if ($open)
                <x-lucide-chevron-down class="size-5" stroke-width="1.25" /> <span class="hidden text-label font-medium uppercase tracking-label sm:inline">Tutup</span>
            @else
                <x-lucide-message-circle class="size-5" stroke-width="1.25" /> <span class="hidden text-label font-medium uppercase tracking-label sm:inline">Chat</span>
            @endif
        </button>
    </div>
</div>

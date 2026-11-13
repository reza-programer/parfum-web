<div>
    @if ($done)
        <span class="meta inline-flex items-center gap-1"><x-lucide-check class="size-3.5" stroke-width="1.5" /> Terima kasih atas ulasanmu</span>
    @elseif (! $open)
        <button type="button" wire:click="$set('open', true)" class="btn-secondary btn-sm">Tulis ulasan</button>
    @else
        <form wire:submit="save" class="card mt-2 w-full space-y-3 p-4 sm:w-80">
            <div class="flex gap-1" role="radiogroup" aria-label="Rating">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" wire:click="$set('rating', {{ $i }})" role="radio" aria-checked="{{ $rating === $i ? 'true' : 'false' }}" aria-label="{{ $i }} bintang"
                            class="inline-flex size-8 items-center justify-center hover:bg-ink/5">
                        <x-lucide-star @class(['size-5', 'fill-amber-400 text-amber-400' => $i <= $rating, 'text-ink-faint' => $i > $rating]) stroke-width="1.5" />
                    </button>
                @endfor
            </div>
            @error('rating') <p class="text-sm text-danger">{{ $message }}</p> @enderror
            <label for="review-{{ $orderItemId }}" class="sr-only">Ulasan</label>
            <textarea id="review-{{ $orderItemId }}" wire:model="comment" rows="3" class="field text-sm" placeholder="Bagaimana aromanya, tahan berapa lama?"></textarea>
            @error('comment') <p class="text-sm text-danger">{{ $message }}</p> @enderror
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm">Kirim ulasan</button>
                <button type="button" wire:click="$set('open', false)" class="btn-secondary btn-sm">Batal</button>
            </div>
        </form>
    @endif
</div>

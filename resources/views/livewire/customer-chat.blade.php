<section class="mt-5 overflow-hidden rounded-3xl border border-[#ece3d9] bg-[#fffdfa] shadow-sm">
    <header class="border-b border-[#ece3d9] px-5 py-5 sm:px-7">
        <p class="text-xs font-semibold uppercase tracking-[.14em] text-[#4f7657]">MeatHub support</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#1f2937]">Chat with us</h1>
        <p class="mt-2 text-sm text-[#716761]">Send us a message. New replies appear automatically.</p>
    </header>

    <div class="flex max-h-[55vh] min-h-72 flex-col-reverse gap-3 overflow-y-auto bg-[#fbf8f3] px-5 py-5 sm:px-7" wire:poll.2s>
        @forelse ($messages as $message)
            @php($isCustomer = $message->sender_id === auth()->id())
            <div @class(['flex', 'justify-end' => $isCustomer])>
                <article @class([
                    'max-w-[85%] rounded-2xl px-4 py-3',
                    'rounded-br-md bg-[#14532d] text-white' => $isCustomer,
                    'rounded-bl-md border border-[#ece3d9] bg-white text-[#292522]' => ! $isCustomer,
                ])>
                    <p class="mb-1 text-xs font-semibold {{ $isCustomer ? 'text-white/70' : 'text-[#4f7657]' }}">
                        {{ $isCustomer ? 'You' : 'MeatHub Support' }}
                    </p>
                    <p class="whitespace-pre-wrap wrap-break-word text-sm">{{ $message->body }}</p>
                    <time class="mt-2 block text-right text-[11px] {{ $isCustomer ? 'text-white/60' : 'text-[#82766e]' }}">{{ $message->created_at->format('M j, g:i A') }}</time>
                </article>
            </div>
        @empty
            <p class="py-16 text-center text-sm text-[#82766e]">Wala pang messages. Paano kami makakatulong?</p>
        @endforelse
    </div>

    <form wire:submit="sendMessage" class="border-t border-[#ece3d9] p-4 sm:p-5">
        <label for="chat-message" class="sr-only">Your message</label>
        <div class="flex items-end gap-3">
            <textarea id="chat-message" wire:model="body" rows="2" maxlength="2000" required placeholder="Type your message…" class="min-h-12 flex-1 resize-y rounded-xl border border-[#e8dfd5] bg-white px-4 py-3 text-sm outline-none focus:border-[#16a34a] focus:ring-2 focus:ring-[#16a34a]/10"></textarea>
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-[#14532d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#166534] disabled:opacity-60">Send</button>
        </div>
        @error('body')
            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </form>
</section>

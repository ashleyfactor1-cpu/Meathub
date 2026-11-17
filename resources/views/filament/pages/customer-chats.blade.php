<x-filament-panels::page>
    <div wire:poll.2s class="grid min-h-[65vh] gap-4 lg:grid-cols-[18rem_minmax(0,1fr)]">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <h2 class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-950 dark:border-gray-700 dark:text-white">Customer conversations</h2>
            <div class="max-h-[65vh] overflow-y-auto">
                @forelse ($customers as $customer)
                    <button
                        type="button"
                        wire:click="selectCustomer({{ $customer->id }})"
                        @class([
                            'block w-full border-b border-gray-100 px-4 py-3 text-left transition dark:border-gray-800',
                            'bg-primary-50 dark:bg-primary-950' => $selectedCustomer?->id === $customer->id,
                            'hover:bg-gray-50 dark:hover:bg-gray-800' => $selectedCustomer?->id !== $customer->id,
                        ])
                    >
                        <span class="block truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $customer->name }}</span>
                        <span class="mt-1 block truncate text-xs text-gray-500 dark:text-gray-400">{{ $customer->latestChatMessage?->body }}</span>
                    </button>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Wala pang customer chats.</p>
                @endforelse
            </div>
        </section>

        <section class="flex min-h-[65vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            @if ($selectedCustomer)
                <header class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-950 dark:text-white">{{ $selectedCustomer->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedCustomer->email }}</p>
                </header>

                <div class="flex max-h-[52vh] min-h-72 flex-1 flex-col-reverse gap-3 overflow-y-auto bg-gray-50 px-5 py-4 dark:bg-gray-950">
                    @forelse ($messages as $message)
                        @php($isAdmin = $message->sender->is_admin)
                        <div @class(['flex', 'justify-end' => $isAdmin])>
                            <article @class([
                                'max-w-[85%] rounded-2xl px-4 py-3',
                                'rounded-br-md bg-primary-600 text-white' => $isAdmin,
                                'rounded-bl-md border border-gray-200 bg-white text-gray-950 dark:border-gray-700 dark:bg-gray-800 dark:text-white' => ! $isAdmin,
                            ])>
                                <p class="mb-1 text-xs font-semibold {{ $isAdmin ? 'text-white/70' : 'text-primary-700 dark:text-primary-300' }}">
                                    {{ $isAdmin ? 'You' : $selectedCustomer->name }}
                                </p>
                                <p class="whitespace-pre-wrap wrap-break-word text-sm">{{ $message->body }}</p>
                                <time class="mt-2 block text-right text-[11px] {{ $isAdmin ? 'text-white/60' : 'text-gray-500 dark:text-gray-400' }}">{{ $message->created_at->format('M j, g:i A') }}</time>
                            </article>
                        </div>
                    @empty
                        <p class="py-16 text-center text-sm text-gray-500 dark:text-gray-400">Wala pang messages sa conversation na ito.</p>
                    @endforelse
                </div>

                <form wire:submit="sendMessage" class="mt-auto border-t border-gray-200 p-4 dark:border-gray-700">
                    <label for="admin-chat-message" class="sr-only">Reply to customer</label>
                    <div class="flex items-end gap-3">
                        <textarea id="admin-chat-message" wire:model="body" rows="2" maxlength="2000" required placeholder="Write a reply…" class="min-h-12 flex-1 resize-y rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-950 outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500 disabled:opacity-60">Send reply</button>
                    </div>
                    @error('body')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </form>
            @else
                <div class="flex flex-1 items-center justify-center p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Pumili ng customer conversation. Lalabas dito ang mga bagong chat nang kusa.
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>

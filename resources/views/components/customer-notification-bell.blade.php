@auth
    @php
        $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
        $recentNotifications = auth()->user()->notifications()->latest()->limit(5)->get();
    @endphp
    <details class="relative">
        <summary
            aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' unread' : '' }}"
            class="relative inline-flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full border border-[#d9e3d8] bg-white text-[#14532d] transition hover:bg-[#f4f8f3] focus:outline-none focus:ring-2 focus:ring-[#14532d]/30 [&::-webkit-details-marker]:hidden"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.31 6.022 23.848 23.848 0 0 0 5.453 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            @if ($unreadNotificationCount > 0)
                <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-[#a34f3e] px-1 text-[10px] font-bold leading-none text-white">
                    {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                </span>
                <span class="sr-only">{{ $unreadNotificationCount }} unread {{ \Illuminate\Support\Str::plural('notification', $unreadNotificationCount) }}</span>
            @endif
        </summary>
        <section class="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-[#ece3d9] bg-[#fffdfa] text-left shadow-xl sm:w-96" aria-label="Recent notifications">
            <div class="flex items-center justify-between border-b border-[#ece3d9] px-4 py-3">
                <h2 class="font-semibold text-[#1f2937]">Notifications</h2>
                @if ($unreadNotificationCount > 0)
                    <span class="rounded-full bg-[#f8e9e4] px-2.5 py-1 text-xs font-semibold text-[#9f1239]">
                        {{ $unreadNotificationCount }} unread
                    </span>
                @endif
            </div>
            <div class="max-h-80 divide-y divide-[#ece3d9] overflow-y-auto">
                @forelse ($recentNotifications as $notification)
                    @php
                        $data = $notification->data;
                    @endphp
                    <article @class(['px-4 py-3', 'bg-[#f4f8f3]' => is_null($notification->read_at)])>
                        @if (! empty($data['order_number']))
                            <a href="{{ route('account.orders.show', $data['order_number']) }}" class="text-sm font-semibold text-[#14532d] hover:underline">{{ $data['title'] ?? 'Order update' }}</a>
                        @else
                            <p class="text-sm font-semibold text-[#1f2937]">{{ $data['title'] ?? 'MeatHub notification' }}</p>
                        @endif
                        <p class="mt-1 text-sm leading-5 text-[#655c56]">{{ $data['body'] ?? '' }}</p>
                        <time class="mt-1.5 block text-xs text-[#82766e]">{{ $notification->created_at->diffForHumans() }}</time>
                    </article>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-[#716761]">No notifications yet.</p>
                @endforelse
            </div>
            @if ($unreadNotificationCount > 0)
                <form action="{{ route('account.notifications.read-all') }}" method="POST" class="border-t border-[#ece3d9] p-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-[#14532d] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#166534]">
                        Mark all as read
                    </button>
                </form>
            @endif
        </section>
    </details>
@endauth

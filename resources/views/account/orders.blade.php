<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My orders — MeatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5ef] px-4 py-10 text-[#292522] antialiased sm:py-16">
    <main class="mx-auto max-w-3xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-[#14532d] hover:underline">← Back to MeatHub</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-[#14532d] hover:underline">My orders</a>
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-[#655c56] hover:text-[#14532d]">Sign out</button>
                </form>
            </div>
        </div>
        <section class="mt-5 rounded-3xl border border-[#ece3d9] bg-[#fffdfa] p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-[#4f7657]">Customer account</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1f2937]">Hello, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-[#716761]">Review your order numbers, details, and current status below.</p>

            <section class="mt-8" aria-labelledby="orders-heading">
                <h2 id="orders-heading" class="text-xl font-semibold text-[#1f2937]">Your orders</h2>
                @forelse ($orders as $order)
                    @php
                        $statusColor = match ($order->payment_status) {
                            'paid', 'completed' => 'bg-[#edf1e8] text-[#3f6212]',
                            'cancelled' => 'bg-[#f8e9e4] text-[#9f1239]',
                            default => 'bg-amber-50 text-amber-900',
                        };
                    @endphp
                    <a href="{{ $orderUrls[$order->id] }}" class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#ece3d9] bg-white p-4 transition hover:border-[#c89a8c]">
                        <span>
                            <strong class="block text-sm text-[#1f2937]">{{ $order->order_number }}</strong>
                            <span class="mt-1 block text-xs text-[#82766e]">{{ $order->created_at->format('M j, Y') }} · ₱{{ number_format((float) $order->total, 2) }}</span>
                        </span>
                        <span @class(['rounded-full px-3 py-1 text-xs font-semibold', $statusColor])>{{ $order->payment_status_label }}</span>
                    </a>
                @empty
                    <p class="mt-3 rounded-xl border border-[#ece3d9] bg-white p-4 text-sm text-[#716761]">Your account does not have any orders yet.</p>
                @endforelse
            </section>
        </section>
    </main>
</body>
</html>

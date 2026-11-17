<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#14532d">
    <title>Order {{ $order->order_number }} — MeatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5ef] px-4 py-10 text-[#292522] antialiased sm:py-16">
    <main class="mx-auto max-w-2xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-[#14532d] hover:underline">← Back to MeatHub</a>
            <div class="flex items-center gap-3">
                @if (auth()->check() && $order->user_id === auth()->id())
                    <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-[#14532d] hover:underline">My orders</a>
                @endif
                @include('components.customer-notification-bell')
            </div>
        </div>
        <section class="mt-5 rounded-3xl border border-[#ece3d9] bg-[#fffdfa] p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-[#4f7657]">Order update</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1f2937]">Thank you for your order.</h1>
            @php
                $statusColor = match ($order->payment_status) {
                    'paid', 'completed' => 'bg-[#edf1e8] text-[#3f6212]',
                    'cancelled' => 'bg-[#f8e9e4] text-[#9f1239]',
                    default => 'bg-amber-50 text-amber-900',
                };
            @endphp
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <p class="text-sm text-[#716761]">Order <strong>{{ $order->order_number }}</strong></p>
                <span @class(['rounded-full px-3 py-1 text-xs font-semibold', $statusColor])>{{ $order->payment_status_label }}</span>
            </div>
            <p class="mt-3 rounded-xl border border-[#dce8dd] bg-[#f4f8f3] p-4 text-sm text-[#14532d]">
                Your order {{ $order->order_number }} is now {{ $order->payment_status_label }}. Refresh this page to see the latest status.
            </p>

            <div class="mt-6 divide-y divide-[#ece3d9] border-y border-[#ece3d9]">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="text-[#514740]">{{ $item->product_name }} · {{ $item->quantity_kg }} kg</span>
                        <strong class="text-[#14532d]">₱{{ number_format((float) $item->subtotal, 2) }}</strong>
                    </div>
                @endforeach
                <div class="flex items-center justify-between py-4">
                    <span class="font-semibold">Total to pay</span>
                    <strong class="text-xl text-[#14532d]">₱{{ number_format((float) $order->total, 2) }}</strong>
                </div>
            </div>

            @if ($producers->isNotEmpty())
                <section class="mt-7" aria-labelledby="producer-locations-heading">
                    <h2 id="producer-locations-heading" class="text-lg font-semibold text-[#1f2937]">Producer shop / pick-up locations</h2>
                    <div class="mt-3 grid gap-5">
                        @foreach ($producers as $producer)
                            <article class="rounded-2xl border border-[#ece3d9] bg-white p-4">
                                <h3 class="font-semibold text-[#1f2937]">{{ $producer->name }}</h3>
                                @include('components.producer-map', ['producer' => $producer])
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($order->payment_status === 'pending')
                @if ($gcashQrImage)
                    <div class="mt-7 rounded-2xl border border-[#dce8dd] bg-[#f4f8f3] p-5 text-center">
                        <h2 class="text-lg font-semibold text-[#14532d]">Pay with GCash</h2>
                        <p class="mt-1 text-sm text-[#655c56]">Scan the merchant QR code and send the exact order total.</p>
                        <img src="{{ asset($gcashQrImage) }}" alt="MeatHub GCash payment QR code" class="mx-auto mt-4 aspect-square w-56 rounded-xl bg-white object-contain p-2">
                        <p class="mt-4 text-sm text-[#514740]">After payment, submit the GCash transaction reference below. We will verify it manually.</p>
                        @if ($order->payment_reference)
                            <p class="mt-3 rounded-xl bg-white px-4 py-3 text-sm text-[#514740]">Reference submitted: <strong>{{ $order->payment_reference }}</strong></p>
                            @if ($order->gcash_receipt)
                                <img src="{{ asset('storage/'.$order->gcash_receipt) }}" alt="Uploaded GCash payment receipt" class="mx-auto mt-4 max-h-80 rounded-xl border border-[#ece3d9] object-contain">
                            @endif
                        @else
                            <form action="{{ $paymentReferenceUrl }}" method="POST" enctype="multipart/form-data" class="mt-4 text-left">
                                @csrf
                                <label for="payment-reference" class="block text-sm font-medium text-[#514740]">GCash transaction reference</label>
                                <input id="payment-reference" name="payment_reference" value="{{ old('payment_reference') }}" required maxlength="100" class="mt-2 h-12 w-full rounded-xl border border-[#e8dfd5] bg-white px-4 text-sm outline-none focus:border-[#16a34a] focus:ring-2 focus:ring-[#16a34a]/10">
                                @error('payment_reference')
                                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                                @enderror
                                <label for="gcash-receipt" class="mt-4 block text-sm font-medium text-[#514740]">Payment receipt screenshot</label>
                                <input id="gcash-receipt" name="gcash_receipt" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-2 block w-full rounded-xl border border-[#e8dfd5] bg-white p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf1e8] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#14532d]">
                                <p class="mt-1 text-xs text-[#82766e]">Upload JPG, PNG, or WebP (maximum 5 MB).</p>
                                @error('gcash_receipt')
                                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="mt-3 w-full rounded-xl bg-[#14532d] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#166534]">Submit payment reference</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">The merchant GCash QR code is not configured. Please contact MeatHub before sending payment.</p>
                @endif
            @elseif (in_array($order->payment_status, ['paid', 'completed'], true))
                <p class="mt-6 rounded-xl border border-[#dce8dd] bg-[#f4f8f3] p-4 text-sm text-[#14532d]">Your payment has been verified. We will contact you about pickup.</p>
            @else
                <p class="mt-6 rounded-xl border border-[#ece3d9] bg-[#f5f2ed] p-4 text-sm text-[#655c56]">This order was cancelled.</p>
            @endif
        </section>
    </main>
</body>
</html>
